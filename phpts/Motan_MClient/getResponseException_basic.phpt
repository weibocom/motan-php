--TEST--
Test class Motan\Client method  doCall() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$protocol = DEFAULT_PROTOCOL;
$cx = new Motan\MClient( $app_name, $group, $service, $protocol );
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
string(58) "{"errcode":500,"errmsg":"provider call panic","errtype":1}"
===DONE===
