<h2 align="center">PushWordPressToWeChat</h2>

<p align="center">
基于 PushBear 服务提供 WordPress 内容更新微信订阅推送的插件
<img src="images/push-wordpress-to-wechat.png" alt="push-wordpress-to-wechat" align="center" />
</p>


## 何为PushBear

> ‼️ [PushBear推送服务将在五月底下线](http://sc.ftqq.com/3.version)，影响个人推送服务，有认证过的微信服务号不受影响。

> ` PushBear`的第三方平台申请已经通过，就是说你得有一个认证过的服务号，然后授权给`PushBear`，`PushBear`会用你的服务号来生成订阅二维码和推送模板消息。

`PushBear`是@Easy提供的一个基于微信模板的一对多消息送达服务，高效+超高到达

1. 无需注册，直接[扫码登入](http://pushbear.ftqq.com/admin/#/signin)
2. 创建消息通道，获得订阅二维码
3. 通过 [API](http://pushbear.ftqq.com/admin/#/api) 向关注了该二维码的用户推送消息

推送消息存储`72`小时、`5`分钟内不可发布重复消息、普通用户每天`1000`条上限、请勿用于发送广告和有害信息

> 目前`PushBear`暂时停止创建通道，所以如果没有`key`就无法使用。有的无视，直接使用即可。

## 如何使用

### 安装

1. 下载源码

从`Github`下载源码，通过`WordPress`后台上传安装，或者直接将源码上传到`WordPress`插件目录`wp-content\plugins`，然后在后台启用

2. `WordPress`后台搜索`Push WordPress To WeChat`

> <del>此方法暂时无法使用，正在提交至官方插件库。</del>因为`PushBear`没有开源协议和隐私政策等，官方插件库拒绝添加，所以还是使用方法一吧

### 设置

- 方法一：在`WordPress`后台已安装的插件页面中有设置按钮，，点击进入设置页面
- 方法二：在`WordPress`后台左侧导航栏`设置`下`Push To WeChat`，点击进入设置页面

### 填写配置

进入插件设置页面后，填入`PushBear`创建消息通道后的`SendKey`，自行选择勾选其他参数

![push-wordpress-to-wechat插件截图](images/pwtw-v1.2.2.png)

## 如何订阅

创建消息通道后就可以看到本通道的订阅二维码，将此二维码给需要订阅的用户关注即可。

这是我的博客订阅二维码，欢迎订阅～

<p align="center">
<img src="images/showqrcode.jpeg" alt="push-wordpress-to-wechat" align="center" width="200px" />
</p>

## Todo

* [x] 可填写配置信息，选择是否默认推送
* [x] 可自定义推送标题
* [x] 可自定义推送内容
* [x] 更新文章亦可选择是否推送
* [x] 可多次推送
* [x] 增加`PushBear`错误码，失败给予提示

## 更新日志

<details>
<summary>点击查看</summary>

### 1.2.2

* 按需加载对应预定义变量
* 增加文章特色图片`{img}`预定义变量

### 1.2.1

* 增加推送成功失败提示

> 关于错误码问题，PushBear取消了接口返回值，减少接口调用次数

### 1.2.0

* 增加多次推送，并兼容前两版本
* 增加文章摘要`{excerpt}`预定义变量

### 1.1.0

* 修改默认推送和删除逻辑
* 增加自定义推送标题和内容

### 1.0.0

* 🎉第一个版本现世，为了给博客增加活跃度，顺手写了插件

</details>
