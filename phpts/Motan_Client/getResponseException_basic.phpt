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
$rs = $cx->doCall('HelloX', 222, 123, 124, ['string','arr']);
if (null === $rs) {
    var_dump($cx->getResponseException());
}
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(97) "{"errcode":400,"errmsg":"FailOverHA call fail 1 times.Exception:provider call panic","errtype":1}"
===DONE===
