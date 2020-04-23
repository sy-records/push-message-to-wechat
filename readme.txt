=== Push Message To WeChat ===
Contributors: shenyanzhi
Donate link: https://qq52o.me/sponsor.html
Tags: wechat,push,message,subscribe,pushbear,server
Requires at least: 4.2
Tested up to: 5.4
Requires PHP: 5.6.0
Stable tag: 2.0.0
License: Apache 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0.html

基于PushBear服务提供WordPress内容更新微信订阅推送的插件

== Description ==

基于PushBear推送微信模板消息的一对多消息送达服务，来提供WordPress内容更新微信订阅推送的插件，需要微信认证订阅号/服务号。

## 相关依赖：

* [微信服务号/订阅号](https://mp.weixin.qq.com/)
* [PushBear](http://pushbear.ftqq.com/)

插件使用帮助查看Github：[Push Message To WeChat](https://github.com/sy-records/push-message-to-wechat)

## 插件特色

* 可填写配置信息，选择是否默认推送
* 可自定义推送标题
* 可自定义推送内容
* 更新文章亦可选择是否推送
* 可多次推送
* 增加`PushBear`错误码，失败给予提示

## 作者博客

[沈唁志](https://qq52o.me "沈唁志")

接受定制开发 WordPress 插件，如有定制开发需求可以[联系QQ](http://wpa.qq.com/msgrd?v=3&uin=85464277&site=qq&menu=yes)。

== Installation ==

1. 把 PushMessageToWeChat 文件夹上传到 /wp-content/plugins/ 目录下
2. 在后台插件列表中激活 PushMessageToWeChat
3. 开始使用吧~

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png

== Changelog ==

= 2.0.0 =

* 发布至官方插件库 [Push Message To WeChat](https://wordpress.org/plugins/push-message-to-wechat/)

= 1.2.2 =

* 按需加载对应预定义变量
* 增加文章特色图片`{img}`预定义变量

= 1.2.1 =

* 增加推送成功失败提示

> 关于错误码问题，PushBear取消了接口返回值，减少接口调用次数

= 1.2.0 =

* 增加多次推送，并兼容前两版本
* 增加文章摘要`{excerpt}`预定义变量

= 1.1.0 =

* 修改默认推送和删除逻辑
* 增加自定义推送标题和内容

= 1.0.0 =

* 🎉第一个版本现世，为了给博客增加活跃度，顺手写了插件
