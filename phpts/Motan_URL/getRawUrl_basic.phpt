--TEST--
Test class Motan\URL method  getRawUrl() by calling it with its expected arguments
--FILE--
<?php
include(dirname(__FILE__) . '/url.inc');

var_dump( $class->getRawUrl() );
?>
--EXPECTF--
string(27) "http://weibo.com:90/add?g=x"
