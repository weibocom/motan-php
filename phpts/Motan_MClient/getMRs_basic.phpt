--TEST--
Test class Motan\MClient method  getMRs() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$protocol = DEFAULT_PROTOCOL;
$cx = new Motan\MClient( $app_name, $service, $group, $protocol );
$request = new \Motan\Request($service, 'Hello', ['a'=>'b']);
$cx->doMultiCall([$request]);

var_dump( $cx->getMRs( $request ) );
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(26) "[]-------[128 1 2 128 1 2]"
===DONE===
