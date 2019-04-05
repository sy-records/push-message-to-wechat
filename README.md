<h2 align="center">PushWordPressToWeChat</h2>

<p align="center">
基于 PushBear 服务提供 WordPress 内容更新微信订阅推送的插件
<img src="./push-wordpress-to-wechat.png" alt="push-wordpress-to-wechat" align="center" />
</p>


## 何为PushBear

`PushBear`是@Easy提供的一个基于微信模板的一对多消息送达服务，高效+超高到达

1. 无需注册，直接[扫码登入](http://pushbear.ftqq.com/admin/#/signin)
2. 创建消息通道，获得订阅二维码
3. 通过 [API](http://pushbear.ftqq.com/admin/#/api) 向关注了该二维码的用户推送消息

推送消息存储`72`小时、`5`分钟内不可发布重复消息、普通用户每天`1000`条上限、请勿用于发送广告和有害信息

## 如何使用

### 安装

1. 下载源码

从`Github`下载源码，通过`WordPress`后台上传安装，或者直接将源码上传到`WordPress`插件目录`wp-content\plugins`，然后在后台启用

2. `WordPress`后台搜索`Push WordPress To WeChat`

> 此方法暂时无法使用，正在提交至官方插件库

### 设置

- 方法一：在`WordPress`后台已安装的插件页面中有设置按钮，，点击进入设置页面
- 方法二：在`WordPress`后台左侧导航栏`设置`下`Push To WeChat`，点击进入设置页面

### 填写配置

进入插件设置页面后，填入`PushBear`创建消息通道后的`SendKey`，自行选择勾选其他参数

![push-wordpress-to-wechat插件截图](./screenshot-1.png)

## 如何订阅

创建消息通道后就可以看到本通道的订阅二维码，将此二维码给需要订阅的用户关注即可

<p align="center">
<img src="./showqrcode.jpeg" alt="push-wordpress-to-wechat" align="center" width="200px" />
这是我的博客订阅二维码，欢迎订阅～
</p>
