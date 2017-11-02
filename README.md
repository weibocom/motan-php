# Motan-PHP
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/weibocom/motan/blob/master/LICENSE)

# Overview
[Motan][motan] is a cross-language remote procedure call(RPC) framework for rapid development of high performance distributed services.

This project is the PHP Motan implementation. Provides PHP motan client.


# Quick Start

## Installation

```sh
composer require
```

The quick start gives very basic example of running client and server on the same machine. For the detailed information about using and developing Motan, please jump to [Documents](#documents).
the demo case is in the main/ directory

## Motan server
TBD

## Motan client

1. demo

```php
define('D_CONN_DEBUG', '10.211.55.3:1234');
$url_str = 'motan2://127.0.0.1:9983/com.weibo.motan.status?group=idevz-test-static';
$url = new \Motan\URL($url_str);
$cx = new \Motan\Client($url);
$rs = $cx->show_batch(['name'=>'idevz']);
if (null === $rs) {
   print_r($cx->getResponseException());
}
print_r($cx->getResponseHeader());
print_r($cx->getResponseMetadata());
print_r($rs);
```

## Use agent. 
TBD

# Documents

TBD

# Contributors

* 周晶([@idevz](https://github.com/idevz))
* 郭万韬
* 丁振凯

# License

Motan is released under the [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0).

[motan]:https://github.com/weibocom/motan
