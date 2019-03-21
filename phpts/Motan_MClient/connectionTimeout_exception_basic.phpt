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
define('D_AGENT_ADDR', '127.0.0.1:9999');
define('D_CONN_DEBUG', '8.8.8.8:80');
define('WEIBOMESH_CONN_RETRY_TIMES', 10);
define('WEIBOMESH_CONN_TIME_OUT',0.001);
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
weibo-mesh isn't alive Connect to 127.0.0.1:9999 fail, err_code:111,err_msg:Connection refused 
string(70) "Connect to 8.8.8.8:80 fail, err_code:110,err_msg:Connection timed out "
===DONE===