--TEST--
Test class Motan\Client method  doCall() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');
$net_devices_time_delay = [
    'lo' => 1000,
];
exec('ls /sys/class/net', $net_devices);
foreach($net_devices as $net_dvc) {
    $delay_time = isset($net_devices_time_delay[$net_dvc]) ? $net_devices_time_delay[$net_dvc] : 10;
    exec("tc qdisc add dev ${net_dvc} root netem delay ${delay_time}ms");
}

$app_name = 'phpt-test-MClient';
$group = DEFAULT_GROUP;
$service = DEFAULT_SERVICE;
$protocol = DEFAULT_PROTOCOL;
define('D_CONN_DEBUG', '8.8.8.8:53');
define('WEIBOMESH_CONN_TIME_OUT', 0.0005);
define('WEIBOMESH_CONN_RETRY_TIMES', 1);
$cx = new Motan\MClient( $app_name );
$cx->setRequestTimeOut(0.000001);
$request = new \Motan\Request($service, 'HelloW', ['test'=>'time_out']);
$request->setGroup(DEFAULT_GROUP);
try{
    $cx->doCall($request);
} catch(Exception $e) {
    var_dump($e->getMessage());
}

foreach($net_devices as $net_dvc) {
    exec("tc qdisc del dev ${net_dvc} root");
}
?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
weibo-mesh isn't alive Connect to 127.0.0.1:9981 fail, err_code:110,err_msg:Connection timed out 
string(70) "Connect to 8.8.8.8:53 fail, err_code:110,err_msg:Connection timed out "
===DONE===