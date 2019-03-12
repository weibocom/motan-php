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
$cx = new Motan\MClient( $app_name, $service, $group, $protocol );
$rs = $cx->doCall('HelloX', 'string', 123, 124, ['string','arr']);

var_dump($rs);
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(46) "strArg:string-inT64:123-int32:124-[string arr]"
===DONE===
