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
string(355) "{"errcode":400,"errmsg":"No referers for request, RequestID: 1547183298596160152, Request info: map[requestIdFromClient:1547183298596160152 M_rid:1547183298596160152 M_p:cn.sina.api.commons.cache.MemcacheClient M_pp:motan2 M_s:phpt-test-Client CS_ns:test_cacheservice_namespace SERIALIZATION:simple M_m:set M_g:motan-demo-rpc host:127.0.0.1]","errtype":1}"
NULL
===DONE===
