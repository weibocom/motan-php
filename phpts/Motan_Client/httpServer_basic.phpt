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
$service = 'com.weibo.HelloWorldService';
$protocol = DEFAULT_PROTOCOL;
define('WEIBOMESH_CONN_RETRY_TIMES', 10);

$motan2_url = 'motan2://127.0.0.1:9981/com.weibo.HelloWorldService?group=motan-demo-rpc';
$motan2_cx = new Motan\Client( new Motan\URL($motan2_url) );
try{
    $motan2_res = $motan2_cx->anyFuncName();
} catch(Exception $e) {
    var_dump($e->getMessage());
}
var_dump($motan2_res);


$cedrus_url = 'cedrus://127.0.0.1:9981/?service=com.weibo.HelloWorldService&group=motan-demo-rpc';
$cedrus_cx = new Motan\Client( new Motan\URL($cedrus_url) );
try{
    $cedrus_res = $cedrus_cx->get();
} catch(Exception $e) {
    var_dump($e->getMessage());
}
var_dump($cedrus_res);


$http_url = 'http://127.0.0.1:9981/?service=com.weibo.HelloWorldService&group=motan-demo-rpc';
$http_cx = new Motan\Client( new Motan\URL($http_url) );
try{
    $http_res = $http_cx->get();
} catch(Exception $e) {
    var_dump($e->getMessage());
}
var_dump($http_res);

?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(31) "http server provider by golang."
string(31) "http server provider by golang."
string(31) "http server provider by golang."
===DONE===