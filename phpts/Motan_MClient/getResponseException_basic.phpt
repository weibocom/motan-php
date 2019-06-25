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
$cx = new Motan\MClient( $app_name );
$request = new \Motan\Request($service, 'HelloX', 222, 123, 124, ['string','arr']);
$request->setGroup(DEFAULT_GROUP);
$rs = $cx->doCall($request);
if (null === $rs) {
    var_dump($cx->getResponseException());
}
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(99) "{"errcode":500,"errmsg":"FailOverHA call fail 1 times. Exception: provider call panic","errtype":1}"
===DONE===
