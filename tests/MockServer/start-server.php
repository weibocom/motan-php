<?php
require __DIR__."/../../vendor/autoload.php";

use MockServer\Server;

include_once __DIR__."/BasicService.php";
include_once __DIR__."/Server.php";
include_once __DIR__."/Socket.php";
include_once __DIR__ . "/GrpcPbService.php";
include_once __DIR__."/BreezeService.php";
include_once __DIR__."/SimpleService.php";

$server = new Server();
$server->listen();
