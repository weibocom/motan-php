--TEST--
Test class Motan\MClient method  getMRs() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$cx = new Motan\MClient( $app_name );
$req1 = new \Motan\Request($service, 'Hello', ['a' => 'b']);
$req2 = new \Motan\Request($service, 'Hello', ['xx' => 'wwww']);
$req3 = new \Motan\Request($service, 'HelloX', 33, 123,124,['string','arr']);
// $req3 = new \Motan\Request($service, 'HelloX', 'string', 123,124,['string','arr']);
$req1->setGroup(DEFAULT_GROUP);
$req2->setGroup(DEFAULT_GROUP);
$req3->setGroup(DEFAULT_GROUP);
$rs = $cx->doMultiCall([
    $req1, $req2, $req3
]);
var_dump($rs->getException($req3));
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(99) "{"errcode":400,"errmsg":"FailOverHA call fail 1 times. Exception: provider call panic","errtype":1}"
===DONE===
