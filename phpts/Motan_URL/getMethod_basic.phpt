--TEST--
Test class Motan\URL method  getMethod() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php

include(dirname(__FILE__) . '/url.inc');

var_dump( $class->getMethod(  ) );



?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
string(4) "/add"
===DONE===
