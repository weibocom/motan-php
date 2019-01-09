--TEST--
Test class Motan\MClient method  getMRs() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$protocol = DEFAULT_PROTOCOL;
$cx = new Motan\MClient( $app_name, $group, $service, $protocol );
$req1 = new \Motan\Request($service, 'Hello', ['a' => 'b']);
$req2 = new \Motan\Request($service, 'Hello', ['xx' => 'wwww']);
$req3 = new \Motan\Request($service, 'HelloX', [33, 123,124,['string','arr']]);
// $req3 = new \Motan\Request($service, 'HelloX', ['string', 123,124,['string','arr']]);
$rs = $cx->doMultiCall([
    $req1, $req2, $req3
]);
var_dump($cx->getMException($req3));
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(58) "{"errcode":500,"errmsg":"provider call panic","errtype":1}"
===DONE===
