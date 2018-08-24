--TEST--
Test class Motan\URL method  getService() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php

include(dirname(__FILE__) . '/url.inc');

var_dump( $class->getService(  ) );

?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
NULL
===DONE===