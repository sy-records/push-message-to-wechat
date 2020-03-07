<?php
/*
Plugin Name: Push Message To WeChat
Plugin URI: https://github.com/sy-records/push-message-to-wechat
Description: 基于PushBear服务提供WordPress内容更新微信订阅推送的插件
Version: 2.0.0
Author: 沈唁
Author URI: https://qq52o.me
License: Apache 2.0
*/

add_action('init', 'pmtw_add_post_submit_type', 100);
// 自定义文章类型
function pmtw_add_post_submit_type()
{
    add_action('save_post', 'pmtw_post_submit', 10, 2);
    add_filter('manage_post_posts_columns', 'pmtw_submit_add_post_columns');
    add_action('manage_posts_custom_column', 'pmtw_submit_render_post_columns', 10, 2);
}

function pmtw_getDefinition($definition, $str)
{
    if (strpos($str, $definition) !== false) {
        return true;
    } else {
        return false;
    }
}

function pmtw_post_submit($post_ID, $post)
{
    if (isset($_POST['Pmtw_Submit_CHECK'])) {
        // 判断文章状态
        if ($post->post_status != 'publish') {
            return;
        }

        $pmtw_submit = get_post_meta($post_ID, 'Pmtw_Submit', true);
        $status = sanitize_key(intval($_POST['pmtw_status']));

        // 判断是否设置新增 首次推送 || 推送失败
        if (empty($pmtw_submit) || $pmtw_submit == '-1' || $pmtw_submit == 'OK') {
            if (!empty($status)) {
                update_post_meta($post_ID, 'Pmtw_Submit', 'first_submit');
            }
        }

        // 执行
        if ($status) {
            // 获取选项
            $option = get_option('PushMessageToWeChat');

            $author_id = $post->post_author;
            $author = get_user_meta($author_id, 'nickname', true);
            $title = get_the_title($post_ID); // 微信推送信息标题
            $wx_post_link = get_permalink($post_ID) . '?from=pushbear'; // 文章链接

            // {username} 作者名称 {title} 文章标题 {url} 文章链接 {excerpt} 文章摘要 {img} 图片
            $definition = ["{username}", "{title}", "{url}", "<br>", "{excerpt}", "{img}"];

            if (empty($option['Title'])) {
                $text = "{$author}居然更新文章啦。";
            } else { // 用户自定义标题
                $text = $option['Title'];
                foreach ($definition as $key => $item) {
                    $de_status = pmtw_getDefinition($item, $text);
                    if ($de_status) {
                        switch ($item) {
                            case "{username}":
                                $text = str_replace("{username}", $author, $text);
                                break;
                            case "{title}":
                                $text = str_replace("{title}", $title, $text);
                                break;
//                            case "{url}":
                            // 标题要链接好像没什么用？
//                                $text = str_replace("{url}", $wx_post_link, $text);
//                                break;
                        }
                    }
                }
            }

            if (empty($option['Content'])) {
                $desp = "点击阅读吧~ [$title]($wx_post_link)";
            } else { // 用户自定义内容
                $desp = $option['Content'];
                foreach ($definition as $key => $item) {
                    $de_status = pmtw_getDefinition($item, $desp);
                    if ($de_status) {
                        switch ($item) {
                            case "{username}":
                                $desp = str_replace("{username}", $author, $desp);
                                break;
                            case "{title}":
                                $desp = str_replace("{title}", $title, $desp);
                                break;
                            case "{url}":
                                $desp = str_replace("{url}", $wx_post_link, $desp);
                                break;
                            case "<br>":
                                $desp = str_replace("<br>", "\n", $desp);
                                break;
                            case "{excerpt}":
                                $desp = str_replace("{excerpt}", pmtw_get_post_excerpt($post), $desp);
                                break;
                            case "{img}":
                                $desp = str_replace("{img}", pmtw_get_post_first_img($post), $desp);
                                break;
                        }
                    }
                }
            }

            $request = new WP_Http;
            $api_url = 'https://pushbear.ftqq.com/sub';
            $body = array(
                'sendkey' => $option['SendKey'],
                'text' => $text,
                'desp' => $desp
            );
            $headers = 'Content-type: application/x-www-form-urlencoded';
            $result = $request->post(
                $api_url,
                array(
                    'body' => $body,
                    'headers' => $headers
                )
            );

            if (!is_wp_error($result)) {
                $res = json_decode($result['body'], true);
                if ($res['code'] == 0) {
                    $pmtw_post_submit = get_post_meta($post_ID, 'Pmtw_Submit', true);
                    if ($pmtw_post_submit == 'first_submit') {
                        update_post_meta($post_ID, 'Pmtw_Submit', 1);
                    } else {
                        update_post_meta($post_ID, 'Pmtw_Submit', $pmtw_post_submit + 1);
                    }

                    set_transient("pmtw_pushbear_status", "true");
                } else {
                    set_transient("pmtw_pushbear_status", $res['message']);
                }
            } else {
                update_post_meta($post_ID, 'Pmtw_Submit', '-1');

                set_transient("pmtw_pushbear_status", "false");
            }
        }
    }
}

add_action('admin_notices', 'pmtw_pushbear_status_notices');
function pmtw_pushbear_status_notices()
{
    $status = get_transient("pmtw_pushbear_status");
    if (!empty($status)) {
        if ($status == "true") {
            echo '<div class="notice notice-success is-dismissible"><p>微信订阅推送成功～</p></div>';
        } elseif ($status == "false") {
            echo '<div class="notice notice-error is-dismissible"><p>微信订阅推送失败，原因：' . $status . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>微信订阅推送失败，原因： WordPress Error</p></div>';
        }

        delete_transient("pmtw_pushbear_status");
    }
}

function pmtw_get_post_excerpt($post)
{
    if ($post->post_excerpt) {
        $excerpt = $post->post_excerpt;
    } else {
        if (preg_match('/<p>(.*)<\/p>/iU', trim(strip_tags($post->post_content, "<p>")), $result)) {
            $post_content = $result['1'];
        } else {
            $post_content_r = explode("\n", trim(strip_tags($post->post_content)));
            $post_content = $post_content_r['0'];
        }
        $excerpt = preg_replace(
            '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,0}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,150}).*#s',
            '$1',
            $post_content
        );
    }
    $excerpt = str_replace(array("\r\n", "\r", "\n"), "", $excerpt);
    return $excerpt;
}

function pmtw_get_post_first_img($post)
{
    // 特色图片 优先获取特色缩略图，否则获取文章首图 其他需要手动增加主题相关方法
    if ($values = get_post_custom_values("cover")) {
        //输出自定义域图片地址
        $values = get_post_custom_values("cover");
        $src = $values[0];
    } elseif (has_post_thumbnail()) {
        //如果有特色缩略图，则输出缩略图地址
        $thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
        $src = $thumbnail_src[0];
    } else {
        preg_match_all('/<img .*?src=[\"|\'](.+?)[\"|\'].*?>/', $post->post_content, $strResult, PREG_PATTERN_ORDER);
        $n = count($strResult[1]);
        if ($n > 0) { // 提取首图
            $src = $strResult[1][0];
        }
    }

//    手动增加主题相关方法
//    if (empty($src)) {
//        $src = c7v5_get_post_cover();
//    }

    return $src;
}

// setting plugin
add_action('admin_menu', 'pmtw_submit_menu');
function pmtw_submit_menu()
{
    add_submenu_page(
        'options-general.php',
        '微信订阅设置',
        'Push To WeChat',
        'manage_options',
        'Push_To_WeChat',
        'pmtw_submit_options',
        ''
    );
}

add_action('admin_menu', 'pmtw_submit_create');
function pmtw_submit_create()
{
    add_action('post_submitbox_misc_actions', 'pmtw_submit_to_publish_metabox');
}

function pmtw_submit_to_publish_metabox()
{
    //获取选项
    $option = get_option('PushMessageToWeChat');
    if ($option['SendKey'] == '') {
        return;
    }
    global $post_ID;

    // 获取选项
    $pmtw_submit = get_post_meta($post_ID, 'Pmtw_Submit', true);
    $checked = $option['Default'] == true ? 'checked="checked"' : '';

    if ($pmtw_submit == '-1') {
        $status = '失败，请重试';
    } elseif (empty($pmtw_submit)) {
        $status = '';
    } else {
        if (is_numeric($pmtw_submit)) {
            $pmtw_submit += 1;
            $status = "第[$pmtw_submit]次";
        } else {
            $status = '';
        }
    }

    $input = '<label for="Pmtw_Submit" class="selectit"><input name="pmtw_status" type="checkbox" value="1" ' . $checked . '>推送' . $status . '</label>';

    echo '<div class="misc-pub-section dashicons-before dashicons-heart"><input name="Pmtw_Submit_CHECK" type="hidden" value="true"> 微信订阅：<span id="submit-span">' . $input . '</span></div>';
}

// 文章列表字段
function pmtw_submit_add_post_columns($columns)
{
    $columns['Pmtw_Submit'] = '微信订阅';
    return $columns;
}

function pmtw_submit_render_post_columns($column_name, $id)
{
    switch ($column_name) {
        case 'Pmtw_Submit':
            $status = get_post_meta($id, 'Pmtw_Submit', true);
            // 兼容前两版本
            if ($status == 'OK') {
                $text = "已推送1次";
            } elseif ($status == '-1') {
                $text = "推送失败";
            } elseif (!empty($status)) {
                $text = "已推送{$status}次";
            } else {
                $text = "未推送";
            }
            echo $text;
            break;
    }
}

// init plugin
add_action('admin_init', 'pmtw_submit_default_options');
function pmtw_submit_default_options()
{
    // 获取选项
    $default = get_option('PushMessageToWeChat');
    if ($default == '') {
        // 设置默认数据
        $default = array(
            'SendKey' => '',
            'Default' => '',
            'Delete' => '',
            'Title' => '',
            'Content' => '',
        );
        //更新选项
        update_option('PushMessageToWeChat', $default);
    }
}

// add plugin link
add_filter('plugin_action_links', 'pmtw_submit_add_links', 10, 2);
function pmtw_submit_add_links($actions, $plugin_file)
{
    static $plugin;
    if (!isset($plugin)) {
        $plugin = plugin_basename(__FILE__);
    }
    if ($plugin == $plugin_file) {
        $settings = array('settings' => '<a href="options-general.php?page=Push_To_WeChat">' . __('Settings') . '</a>');
        $actions = array_merge($settings, $actions);
    }
    return $actions;
}

// stop plugin
function pmtw_stop_option()
{
    $option = get_option('PushMessageToWeChat');
    if ($option['Delete']) {
        delete_option("PushMessageToWeChat");
    }
}

register_deactivation_hook(__FILE__, 'pmtw_stop_option');

// setting page
function pmtw_submit_options()
{
    //保存数据
    if (isset($_POST['PmtwSubmit'])) {
        if (!current_user_can('level_10')) {
            echo '<div class="error" id="message"><p>暂无权限操作</p></div>';
            return;
        }

        $nonce = $_REQUEST['_pmtw_nonce'];
        if (!wp_verify_nonce($nonce, 'Pmtw_Submit')) {
            echo '<div class="error" id="message"><p>非法操作</p></div>';
            return;
        }

        $pmtwOption = array(
            'SendKey' => sanitize_key($_POST['SendKey']),
            'Default' => isset($_POST['Default']) ? sanitize_text_field($_POST['Default']) : false,
            'Delete' => isset($_POST['Delete']) ? sanitize_text_field($_POST['Delete']) : false,
            'Title' => sanitize_text_field($_POST['Title']),
            'Content' => sanitize_textarea_field(stripslashes(trim($_POST['Content']))),
        );

        $res = update_option('PushMessageToWeChat', $pmtwOption);//更新选项
        if ($res) {
            $updated = '设置成功！';
        } else {
            $updated = '设置失败或未更新选项！';
        }
        echo '<div class="updated" id="message"><p>' . $updated . '</p></div>';
    }

    // 获取选项
    $option = get_option('PushMessageToWeChat');
    $default = $option['Default'] !== false ? 'checked="checked"' : '';
    $delete = $option['Delete'] !== false ? 'checked="checked"' : '';

    echo '<div class="wrap">';
    echo '<h2>Push Message To WeChat 微信订阅设置</h2>';
    echo '<form method="post">';
    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<td><input class="all-options" type="hidden" name="_pmtw_nonce" value="' . wp_create_nonce(
            'Pmtw_Submit'
        ) . '"></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">PushBear SendKey</th>';
    echo '<td><input class="all-options" type="text" name="SendKey" value="' . $option['SendKey'] . '" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">推送标题</th>';
    echo '<td><input class="all-options" type="text" name="Title" value="' . $option['Title'] . '" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">推送内容</th>';
    echo '<td><textarea  class="all-options"  name="Content" rows="10">' . $option['Content'] . '</textarea><p class="description"><p class="description" >预定义变量参考：<a href="https://github.com/sy-records/push-message-to-wechat/wiki/%E9%A2%84%E5%AE%9A%E4%B9%89%E5%8F%98%E9%87%8F" target="_blank" rel="nofollow noopener">Github Wiki</a></p></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">是否默认推送</th>';
    echo '<td><label><input value="true" type="checkbox" name="Default" ' . $default . '> 勾选后默认都推送给订阅用户，文章发布时可修改！</label></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">是否删除配置信息</th>';
    echo '<td><label><input value="true" type="checkbox" name="Delete" ' . $delete . '> 勾选后停用插件会删除保存的配置信息，减少数据库垃圾数据！</label></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit">';
    echo '<input type="submit" name="PmtwSubmit" id="submit" class="button button-primary" value="保存更改" />';
    echo '</p>';
    echo '</form>';
    echo '<p><strong>使用提示</strong>：<br>
	1.PushBear SendKey 通过 <a target="_blank" href="http://pushbear.ftqq.com/">PushBear网站</a> > 创建消息通道后获取；<br>
	2.标题不超过 80 个字；内容支持 Emoji 表情，支持 Markdown 语法；<br>
	3.其它相关问题至沈唁志博客 <a target="_blank" href="https://qq52o.me/2650.html">Push Message To WeChat 插件</a> 页面查看使用说明和留言反馈。<br>
	</p>';
    echo '</div>';
}

?>