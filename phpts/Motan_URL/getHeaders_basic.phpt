--TEST--
Test class Motan\URL method  getHeaders() by calling it with its expected arguments
--SKIPIF--
<?php
?>
--INI--

--FILE--
<?php

include(dirname(__FILE__) . '/url.inc');

var_dump( $class->getHeaders(  ) );



?>
===DONE===
--CLEAN--
<?php
?>
--EXPECTF--
NULL
===DONE===