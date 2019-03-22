--TEST--
Test class Motan\MClient method  getMRs() by calling it with its expected arguments

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$cx = new Motan\MClient( $app_name );
$cx->setRequestTimeOut(0.01);
$req1 = new \Motan\Request($service, 'Hello', ['a' => 'b']);
$req2 = new \Motan\Request($service, 'Hello', ['xx' => 'wwww']);
$req3 = new \Motan\Request($service, 'TimeOutErr', ['test' => 'wwww']);
$req1->setGroup(DEFAULT_GROUP);
$req2->setGroup(DEFAULT_GROUP);
$req3->setGroup(DEFAULT_GROUP);
$multi_res = $cx->doMultiCall([
    $req1, $req2, $req3
]);
var_dump($multi_res->getException($req3));
if($multi_res->getException($req1) == NULL) {
    var_dump($multi_res->getRs($req1));
}
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(20) "Read header timeout."
string(26) "[]-------[128 1 2 128 1 2]"
===DONE===
