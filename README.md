# 介绍

脚本用来抓取国家统计局，统计的省市区信息，用做省市区三级联动使用，请合理使用该脚本，爬取数据是违法的！！！

如需2021年数据可直接下载[2021.json]()文件

# 安装使用
1、安装PHP环境并要求PHP版本 >= 7.2

2、克隆项目到服务器或本地

```git clone https://github.com/diaojinlong/get-city.git```

3、安装相关包依赖

```composer install```

4、执行抓取操作，并将结果导出为json文件

```php index.php > 2021.json```

# 常见问题

1、抓取过快可能导致失败，通过修改index.php参数实现请求一次休息几秒，默认2秒。

```$getCityDo->getJsonData(2);```
