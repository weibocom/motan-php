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
$cx = new Motan\MClient( $app_name );
$params = [
    'hello'=>'motan-php',
    'a'=>'b'
];
$request = new \Motan\Request(DEFAULT_SERVICE, 'Hello', $params);
$request->setGroup(DEFAULT_GROUP);
$rs = $cx->doCall($request);
var_dump($rs);
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(26) "[]-------[128 1 2 128 1 2]"
===DONE===
