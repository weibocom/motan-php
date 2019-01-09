--TEST--
Test class Motan\URL method  getHeaders() by calling it with its expected arguments
--INI--

--FILE--
<?php

include(dirname(__FILE__) . '/url.inc');

var_dump($class->getHeaders());

?>
===DONE===
--EXPECTF--
array(1) {
  ["Content-Type"]=>
  string(33) "application/x-www-form-urlencoded"
}
===DONE===
