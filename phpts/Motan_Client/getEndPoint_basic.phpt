--TEST--
Test class Motan\Client method  getEndPoint() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php
require(dirname(__FILE__) . '/../motan.inc.php');
define('APP_NAME', 'phpt-test-Client');

$url_str = 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc';
$url = new \Motan\URL($url_str);
$class = new Motan\Client( $url );




var_dump( $url->getProtocol() );



?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(6) "motan2"
===DONE===
