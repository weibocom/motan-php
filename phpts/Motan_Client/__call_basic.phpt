--TEST--
Test class Motan\Client method  doCall() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

define('APP_NAME', 'phpt-test-Client');

$url_str = 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc';
$url = new \Motan\URL($url_str);
$url->setConnectionTimeOut(50000);
$url->setReadTimeOut(50000);
$cx = new \Motan\Client($url);
$params = [
    'hello'=>'motan-php',
    'a'=>'b'
];
$rs = $cx->Hello($params);
var_dump($rs);
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(26) "[]-------[128 1 2 128 1 2]"
===DONE===
