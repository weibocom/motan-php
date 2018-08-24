--TEST--
Test class Motan\URL method  getParams() by calling it with its expected arguments
--FILE--
<?php
include(dirname(__FILE__) . '/url.inc');

var_dump( $class->getParams(  ) );



?>
===DONE===
--EXPECTF--
array(1) {
  ["g"]=>
  string(1) "x"
}
===DONE===
