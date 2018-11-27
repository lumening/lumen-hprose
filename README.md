# Lumen-hprose

基于 [hprose/hprose-php](https://github.com/hprose/hprose-php/wiki) 开发的Lumen/Laravel扩展：[Lumen-hprose](https://github.com/lumening/lumen-hprose)
参考：[Laravel-hprose](https://github.com/zhuqipeng/laravel-hprose) 

开发背景：最近打算使用lumen框架做rpc的功能，于是在网上找是否有相关的拓展，于是找到了Laravel-hprose，本打算直接用于 lumen 但尝试后发现laravel的不能完全与lumen兼容，于是根据Laravel-hprose 修改 得到 [Lumen-hprose](https://github.com/lumening/lumen-hprose)，同时兼容lumen 和 laravel。

## 版本要求

```
Laravel>=5.2
```

## 安装
编辑composer.json
```json
"repositories": [
        {
            "type": "git",
            "url": "https://github.com/lumening/lumen-hprose.git"
        }
    ]
```
然后执行
```shell
composer require "lumen-hprose"
```

## 使用**laravel**配置
1. 在 config/app.php 注册 ServiceProvider 和 Facade (Laravel 5.5 无需手动注册)
    ```php
    'providers' => [
        // ...

       LumenHprose\ServiceProvider::class,
    ]
    ```
    ```php
    'aliases' => [
        // ...

         'LumenHproseRouter'=>LumenHprose\Facades\Router::class,
    ]
    ```
2. 配置.env文件
    监听地址列表，字符串json格式数组
    ```
    HPROSE_URIS=["tcp://0.0.0.0:8888"]
    ```

    是否启用demo方法，true开启 false关闭，开启后将自动对外发布一个远程调用方法 `demo`
    客户端可调用：$client->demo()
    ```
    HPROSE_DEMO=true // true or false
    ```

3. 创建`配置`和`路由`文件：
    ```shell
    php artisan vendor:publish --provider="LumenHprose\ServiceProvider"
    ```
    >应用根目录下的`config`目录下会自动生成新文件`hprose.php`
    >
    >应用根目录下的`routes`目录下会自动生成新文件`rpc.php`
    
## 使用**lumen**配置
1. 在 bootstrap/app.php 注册 ServiceProvider 和 Facade
    ```php
       $app->register(LumenHprose\ServiceProvider::class);
    ```
    ```php
        $app->withFacades(true, [
            // ...
            'LumenHprose\Facades\Router' => 'LumenHproseRouter',
        ]);
    ```
2. 在 app/Console/Kernel.php 添加 vendor publish
    ```php
        protected $commands = [
        //...
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
        ];
    ```
3. 配置.env文件
    监听地址列表，字符串json格式数组
    ```
    HPROSE_URIS=["tcp://0.0.0.0:8888"]
    ```

    是否启用demo方法，true开启 false关闭，开启后将自动对外发布一个远程调用方法 `demo`
    客户端可调用：$client->demo()
    ```
    HPROSE_DEMO=true // true or false
    ```

4. 创建`配置`和`路由`文件：
    ```shell
    php artisan vendor:publish --provider="LumenHprose\ServiceProvider"
    ```
    >应用根目录下的`config`目录下会自动生成新文件`hprose.php`
    >
    >应用根目录下的`routes`目录下会自动生成新文件`rpc.php`

## 使用

### 路由
>和 `laravel` 路由的用法相似，基于 [dingo/api](https://github.com/dingo/api) 的路由代码上做了简单修改

路由文件
```
routes/rpc.php
```

添加路由方法
```php
\LumenHproseRouter::add(string $name, string|callable $action, array $options = []);
```
- string $name 可供客户端远程调用的方法名
- string|callable $action 类方法，格式：App\Controllers\User@update
- array $options 是一个关联数组，它里面包含了一些对该服务函数的特殊设置，详情请参考hprose-php官方文档介绍 [链接](https://github.com/hprose/hprose-php/wiki/06-Hprose-%E6%9C%8D%E5%8A%A1%E5%99%A8#addfunction-%E6%96%B9%E6%B3%95)

发布远程调用方法 `getUserByName` 和 `update`
```php
\LumenHproseRouter::add('getUserByName', function ($name) {
    return 'name: ' . $name;
});

\LumenHproseRouter::add('userUpdate', 'App\Controllers\User@update', ['model' => \Hprose\ResultMode::Normal]);
```

控制器
```php
<?php

namespace App\Controllers;

class User
{
    public function update($name)
    {
        return 'update name: ' . $name;
    }
}
```

客户端调用 客户端可以只安装 Hprose
```php
$client = new \Hprose\Socket\Client('tcp://127.0.0.1:8888', false);
$client->getUserByName('lumen');
$client->userUpdate('lumen');
```

路由组
```php
\LumenHproseRouter::group(array $attributes, callable $callback);
```
- array $attributes 属性 ['namespace' => '', 'prefix' => '']
- callable $callback 回调函数

```php
\LumenHproseRouter::group(['namespace' => 'App\Controllers'], function ($route) {
    $route->add('getUserByName', function ($name) {
        return 'name: ' . $name;
    });

    $route->add('userUpdate', 'User@update');
});
```
客户端调用
```php
$client->getUserByName('lumen');
$client->userUpdate('lumen');
```

前缀
```php
\LumenHproseRouter::group(['namespace' => 'App\Controllers', 'prefix' => 'user'], function ($route) {
    $route->add('getByName', function ($name) {
        return 'name: ' . $name;
    });

    $route->add('update', 'User@update');
});
```
客户端调用
```php
$client->user->getByName('lumen');
$client->user->update('lumen');
// 或者
$client->user_getByName('lumen');
$client->user_update('lumen');
```

### 启动服务

```shell
php artisan hprose:socket_server
```


