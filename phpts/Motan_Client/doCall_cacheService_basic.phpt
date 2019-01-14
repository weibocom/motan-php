--TEST--
Test class Motan\Client method  doCall() cacheService by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

define('APP_NAME', 'phpt-test-Client');

$url_str = 'motan2://127.0.0.1:9981/cn.sina.api.commons.cache.MemcacheClient?group=motan-demo-rpc';
$url = new \Motan\URL($url_str);
$url->setConnectionTimeOut(50000);
$url->setReadTimeOut(50000);
$url->addHeaders(["CS_ns" => "test_cacheservice_namespace"]);
$url->setProtocol('memcache');
$cx = new \Motan\Client($url);
$rs = $cx->doCall("set", "test123456", "你好，世界,123172397129371927391729837129", 104600);
if (!empty($cx->getResponseException())) {
  var_dump($cx->getResponseException());
}
var_dump($cx->doCall("get", "test123456"));
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(46) "你好，世界,123172397129371927391729837129"
===DONE===
