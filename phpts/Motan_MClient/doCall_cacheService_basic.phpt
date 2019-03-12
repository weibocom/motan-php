--TEST--
Test class Motan\MClient parent method  doCall() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = 'cn.sina.api.commons.cache.MemcacheClient';
$protocol = 'memcache';
$cx = new Motan\MClient( $app_name, $service, $group, $protocol );
$request = new \Motan\Request($service, 'Hello', ['a'=>'b']);
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
