<?php
/*

Plugin Name: Push WordPress To WeChat

Plugin URI: https://qq52o.me/2650.html

Description: 基于 PushBear 服务提供 WordPress 内容更新微信订阅推送的插件

Author: 沈唁

Version: 1.0.0

Author URI: https://qq52o.me

*/

add_action('init', 'PwtwSubmit', 100);
// 自定义文章类型
function PwtwSubmit()
{
    add_action('save_post', 'pwtw_submit', 10, 2);
    add_filter('manage_post_posts_columns', 'pwtw_submit_add_post_columns');
    add_action('manage_posts_custom_column', 'pwtw_submit_render_post_columns', 10, 2);
}

function pwtw_submit($post_ID, $post)
{
    if (isset($_POST['Pwtw_Submit_CHECK'])) {

        $status = sanitize_key(intval($_POST['pwtw_status']));
        $pwtw_submit = get_post_meta($post_ID, 'Pwtw_Submit', true);

        // 判断是否设置新增
        if ($pwtw_submit != 'OK') {
            if ($status == '1') {
                update_post_meta($post_ID, 'Pwtw_Submit', 'first_submit');
            }
        }

        // 判断文章状态与推送状态 返回/继续
        if($post->post_status != 'publish' || $pwtw_submit == 'OK' ) {
            return;
        }

        // 执行
        if ($status) {

            // 获取选项
            $option = get_option('PushWordPressToWeChat');

            $author_id =  get_post($post_ID)->post_author;
            $author = get_user_meta($author_id, 'nickname', true);
            $text = "{$author}居然更新文章啦。";
            $title = get_the_title($post_ID); // 微信推送信息标题
            $wx_post_link = get_permalink($post_ID).'?from=pushbear'; // 文章链接
            $desp = "点击阅读吧~ [$title]($wx_post_link)";

            $request = new WP_Http;
            $api_url = 'https://pushbear.ftqq.com/sub';
            $body = array(
                'sendkey' => $option['SendKey'],
                'text' => $text,
                'desp' => $desp
            );
            $headers = 'Content-type: application/x-www-form-urlencoded';
            $result = $request->post(
                $api_url, array(
                    'body' => $body,
                    'headers' => $headers
                )
            );

            if(!is_wp_error($result)) {
                update_post_meta($post_ID, 'Pwtw_Submit', 'OK');
            } else {
                update_post_meta($post_ID, 'Pwtw_Submit', '-1');
            }
        }

    }

}

// setting plugin
add_action('admin_menu', 'pwtw_submit_menu');
function pwtw_submit_menu()
{
    add_submenu_page('options-general.php', '微信订阅设置', 'Push To WeChat', 'manage_options', 'Push_To_WeChat', 'pwtw_submit_options', '');

}

add_action('admin_menu', 'pwtw_submit_create');
function pwtw_submit_create()
{
    add_action('post_submitbox_misc_actions', 'pwtw_submit_to_publish_metabox');
}

function pwtw_submit_to_publish_metabox()
{
    //获取选项
    $option = get_option('PushWordPressToWeChat');
    if ($option['SendKey'] == '') {
        return;
    }
    global $post_ID;

    // 获取选项
    $pwtw_submit = get_post_meta($post_ID, 'Pwtw_Submit', true);
    $checked = $option['Default'] == 'true' ? 'checked="checked"' : '';

    $pwtw_box = '<label><input name="pwtw_status" type="checkbox" value="1" '.$checked.'>推送</label>';

    if ($pwtw_submit == 'OK') {
        $input = '
			<label for="Pwtw_Submit" class="selectit">成功</label>
		';
    } else {
        $input = '
			<label for="Pwtw_Submit" class="selectit">'.$pwtw_box.'</label>
		';
    }

    echo '<div class="misc-pub-section misc-pub-post-status"><input name="Pwtw_Submit_CHECK" type="hidden" value="true">微信订阅：<span id="submit-span">'.$input.'</span></div>';
}

// 文章列表字段
function pwtw_submit_add_post_columns($columns)
{
    $columns['Pwtw_Submit'] = '微信订阅推送';
    return $columns;
}

function pwtw_submit_render_post_columns($column_name, $id)
{
    switch ($column_name) {
    case 'Pwtw_Submit':
        echo get_post_meta($id, 'Pwtw_Submit', true) == 'OK'  ? '推送成功' : (get_post_meta($id, 'Pwtw_Submit', true) == '-1' ? '推送失败' : '未推送');
        break;
    }
}

// init plugin
add_action('admin_init', 'pwtw_submit_default_options');
function pwtw_submit_default_options()
{
    // 获取选项
    $default = get_option('PushWordPressToWeChat');
    if($default == '' ) {
        // 设置默认数据
        $default = array(
            'SendKey'    => '',
            'Default'    => '',
            'Delete'     => '',
            'Title'     => '',
            'Content'     => '',
        );
        //更新选项
        update_option('PushWordPressToWeChat', $default);
    }
}

// add plugin link
add_filter('plugin_action_links', 'pwtw_submit_add_links', 10, 2);
function pwtw_submit_add_links( $actions, $plugin_file )
{
    static $plugin;
    if (!isset($plugin)) {
        $plugin = plugin_basename(__FILE__);
    }
    if ($plugin == $plugin_file) {
        $settings    = array('settings' => '<a href="options-general.php?page=Push_To_WeChat">' . __('Settings') . '</a>');
        $site_link    = array('support' => '<a href="https://qq52o.me" target="_blank">沈唁志</a>');
        $actions     = array_merge($settings, $actions);
        $actions    = array_merge($site_link, $actions);
    }
    return $actions;
}

// stop plugin
function pwtw_stop_option()
{
    $option = get_option('PushWordPressToWeChat');
    if ($option['Delete']) {
        delete_option("PushWordPressToWeChat");
    }
}
register_deactivation_hook(__FILE__, 'pwtw_stop_option');

// setting page
function pwtw_submit_options()
{
    //保存数据
    if(isset($_POST['PwtwSubmit'])) {

        if(!current_user_can('level_10')){
            echo '<div class="error" id="message"><p>暂无权限操作</p></div>';
            return;
        }

        $nonce = $_REQUEST['_pwtw_nonce'];
        if (!wp_verify_nonce($nonce, 'Pwtw_Submit')) {
            echo '<div class="error" id="message"><p>非法操作</p></div>';
            return;
        }


        $pwtwOption= array(
            'SendKey'    => sanitize_key($_POST['SendKey']),
            'Default'    => sanitize_text_field($_POST['Default']),
            'Delete' => sanitize_text_field($_POST['Delete']),
            'Title'     => '',
            'Content'     => '',
        );

        $res = update_option('PushWordPressToWeChat', $pwtwOption);//更新选项
        if($res) {
            $updated = '设置成功！';
        }else{
            $updated = '设置失败或未更新选项！';
        }
        echo '<div class="updated" id="message"><p>'.$updated.'</p></div>';
    }

    // //获取选项
    $option = get_option('PushWordPressToWeChat');
    $default = $option['Default'] !== '' ? 'checked="checked"' : '';
    $delete = $option['Delete'] !== '' ? 'checked="checked"' : '';

    echo '<div class="wrap">';
    echo '<h2>Push WordPress To WeChat 微信订阅设置</h2>';
    echo '<form method="post">';
    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<td><input class="all-options" type="hidden" name="_pwtw_nonce" value="'.wp_create_nonce('Pwtw_Submit').'"></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">PushBear SendKey</th>';
    echo '<td><input class="all-options" type="text" name="SendKey" value="'.$option['SendKey'].'" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">推送标题</th>';
    echo '<td><input class="all-options" type="text" name="Title" value="'.$option['Title'].'" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">推送内容</th>';
    echo '<td><input class="all-options" type="text" name="Content" value="'.$option['Content'].'" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">是否默认推送</th>';
    echo '<td><label><input value="true" type="checkbox" name="Default" '.$default.'> 勾选后默认都推送给订阅用户，文章发布时可修改！</label></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">是否删除配置信息</th>';
    echo '<td><label><input value="true" type="checkbox" name="Delete" '.$delete.'> 勾选后停用插件会删除保存的配置信息，减少数据库垃圾数据！</label></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit">';
    echo '<input type="submit" name="PwtwSubmit" id="submit" class="button button-primary" value="保存更改" />';
    echo '</p>';
    echo '</form>';
    echo '<p><strong>使用提示</strong>：<br>
	1.PushBear SendKey 通过 <a target="_blank" href="http://pushbear.ftqq.com/admin/#/">PushBear网站</a> > 创建消息通道后获取；<br>
	2.其它相关问题至沈唁志博客 <a target="_blank" href="https://qq52o.me/2650.html">Push WordPress To WeChat 插件</a> 页面查看使用说明和留言反馈。<br>
	</p>';
    echo '</div>';
}
?>