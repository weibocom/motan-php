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
$cx->setRequestTimeOut(0.000001);
$request = new \Motan\Request($service, 'HelloW', ['test'=>'time_out']);
$request->setGroup(DEFAULT_GROUP);
try{
    $cx->doCall($request);
} catch(Exception $e) {
    var_dump($e->getMessage());
}
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(20) "Read header timeout."
===DONE===

