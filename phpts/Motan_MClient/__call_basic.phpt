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
