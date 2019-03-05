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

$url_str1 = 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc&method=Hello&a=a&b=b';
$url_str2 = 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc&method=HelloW&a=a&b=b';
$url1 = new \Motan\URL($url_str1);
$url2 = new \Motan\URL($url_str2);
$cx = new \Motan\Client($url1);
$rs = $cx->multiCall([$url1, $url2]);

var_dump($rs);
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
array(2) {
  [0]=>
  string(26) "[]-------[128 1 2 128 1 2]"
  [1]=>
  string(6) "HelloW"
}
===DONE===
