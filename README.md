php-weather-info-cn
===================

使用 php 编写的、自动从中国天气网 JSON API 获取天气数据的网页

使用说明
--------
### 文件说明
* forecast.php 用于获取天气预报
* live.php 用于获取实况数据
* alarm.php 用于获取灾害预警信息
* forecast.php 显示修正后的18时发布的天气预报

### 提示
* 地区代码可从[中国天气网]查得。
* 建议为您的主机启用 php-cURL 扩展库。

更新日志
--------
### 2012-08-28
* 再次重写 forecast.php，大幅简化代码

### 2012-08-26
* 添加获取实况数据和灾害预警的脚本

### 2012-08-22
* 取消了对 php-cURL 扩展库的强制依赖

许可协议
--------
php-weather-info-cn 遵循 [Apache许可证2.0版] 发布。

发布页面
--------
[php 天气预报代码]

[中国天气网]: http://www.weather.com.cn/
[Apache许可证2.0版]: http://www.apache.org/licenses/LICENSE-2.0
[php 天气预报代码]: http://lyonna.me/2012/01/php-weather-forecast/
