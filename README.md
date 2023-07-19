# Motan-PHP
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/weibocom/motan/blob/master/LICENSE)
[![codecov](https://codecov.io/gh/weibocom/motan-php/branch/master/graph/badge.svg)](https://codecov.io/gh/weibocom/motan-php)
[![PHPUnit for motan-php](https://github.com/weibocom/motan-php/workflows/PHPUnit%20for%20motan-php/badge.svg)](https://github.com/weibocom/motan-php/actions)

# Overview
[Motan][motan] is a cross-language remote procedure call(RPC) framework for rapid development of high performance distributed services.

This project is the PHP Motan implementation. Provides PHP motan client.


# Quick Start

## Installation

**Using composer:**

Just clone this project and add it to your `composer.json`.

**WithOut Composer:**

If you didn't use composer for php libraries management, you would install motan-php by hand,like `git clone`, please check the demo at [motan-example](https://github.com/motan-ecosystem/motan-examples#for-php) .

**Usage:**

we need an defined constant `MOTAN_PHP_ROOT` for load the motan php libs. Just like the demo does.

```php
define('MOTAN_PHP_ROOT', './vendor/motan/motan-php/src/Motan/');
require MOTAN_PHP_ROOT . 'init.php';
```

The quick start gives very basic example of running client and server on the same machine. For the detailed information about using and developing Motan, please jump to [Documents](#documents).
the demo case is in the main/ directory

## Motan server

We use Weibo-Mesh to support a PHP Server, Weibo-Mesh is a local agent writen in Golang. But not only a agent, Wei-Mesh take the ability as service governance. There is an example at [motan-example](https://github.com/motan-ecosystem/motan-examples/tree/master/weibo-mesh)

**_As a CGI agent to php-fpm_**

```yaml
  cgi-mesh-example-helloworld:
    path: com.weibo.motan.HelloWorldService
    export: "motan2:9991"
    provider: cgi
    CGI_HOST: 10.211.55.3
    CGI_PORT: 9000
    CGI_REQUEST_METHOD: GET
    CGI_SCRIPT_FILENAME: /motan-examples/php-server/index.php
    CGI_DOCUMENT_ROOT: /motan-examples/php-server
    basicRefer: mesh-server-basicService
```

**_As a HTTP agent to any HTTP Server_**

```yaml
  http-mesh-example-helloworld:
    path: com.weibo.motan.HelloWorldService
    export: "motan2:9990"
    provider: http
    HTTP_REQUEST_METHOD: GET
    HTTP_URL: http://10.211.55.3:9900/http_server
    basicRefer: mesh-server-basicService
```

## Motan Client

Here is a simple example about Motan Client, it will call a remote service provider by [Weibo-Mesh Testhelper][testhelper], you can find more example in the [phpt tests][phpts], you can just run `./run.sh` to find more.

```php
$app_name = 'search';
$service = 'com.weibo.HelloMTService';
$group = 'motan-demo-rpc';
$remote_method = 'HelloW';
$params = ['idevz'=>'for weibo-mesh'];
$cx = new Motan\MClient( $app_name );
$request = new \Motan\Request($service, $remote_method, $params);
$request->setGroup($group);
try{
    $res = $cx->doCall($request);
} catch(Exception $e) {
    var_dump($e->getMessage());
}
```

# Unit Test

If you use the local php environment to run unit tests, please make sure that your php version is between 7.2 and 7.4, supports functions such as pcntl_fork, posix_kill, pcntl_signal, etc., and downloaded the composer component.
You can run unit tests by following the instructions below。
```shell
git clone https://github.com/weibocom/motan-php.git
cd motan-php
git checkout $(test branch)
composer install
composer test              # just run phpunit
composer test-coverage     # run phpunit and generate report
```

If you do not want to install a php-related environment, it is recommended that you use docker to avoid problems with inconsistent environments.
You can follow the instructions below.
```shell
git clone https://github.com/weibocom/motan-php.git
cd motan-php
git checkout $(test branch)
docker run --rm -it -v $PWD:/mnt --entrypoint="" snail007/php:7.4 bash
cd /mnt
composer install
composer test              # just run phpunit
composer test-coverage     # run phpunit and generate report
```

# Contributors

* 周晶([@idevz](https://github.com/idevz))
* 罗明刚([@lion2luo](https://github.com/lion2luo))
* 郭万韬
* 丁振凯
* 李枨煊([@flyhope](https://github.com/flyhope))
* 吴桦([@cocowh](https://github.com/cocowh))

# License

Motan is released under the [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0).

[motan]:https://github.com/weibocom/motan

Welcome to submit issue and contribute code！