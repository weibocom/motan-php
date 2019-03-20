--TEST--
Test class Motan\MClient method  getMRs() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$cx = new Motan\MClient( $app_name );
$request = new \Motan\Request($service, 'Hello', ['a'=>'b']);
$request->setGroup(DEFAULT_GROUP);
$multi_res = $cx->doMultiCall([$request]);

var_dump( $multi_res->getRs( $request ) );
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(26) "[]-------[128 1 2 128 1 2]"
===DONE===
