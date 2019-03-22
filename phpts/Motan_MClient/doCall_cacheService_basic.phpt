--TEST--
Test class Motan\MClient parent method  doCall() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = 'cn.sina.api.commons.cache.MemcacheClient';
$protocol = 'memcache';
$cx = new Motan\MClient( $app_name );
$set_request = new \Motan\Request($service, 'set', "test123456", "你好，世界,123172397129371927391729837129", 104600);
$set_request->setGroup(DEFAULT_GROUP);
$set_request->setProtocol($protocol);
$rs = $cx->doCall($set_request);

if (!empty($cx->getResponseException())) {
  var_dump($cx->getResponseException());
}

$get_request = new \Motan\Request($service, 'get', "test123456");
$get_request->setGroup(DEFAULT_GROUP);
$get_request->setProtocol($protocol);
var_dump($cx->doCall($get_request));

if (!empty($cx->getResponseException())) {
  var_dump($cx->getResponseException());
}
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(46) "你好，世界,123172397129371927391729837129"
===DONE===
