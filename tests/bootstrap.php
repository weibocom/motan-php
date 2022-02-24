<?php

use MockServer\Server;

require __DIR__ . '/../vendor/autoload.php';
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
isset($_SERVER['CONN_HOST_IP']) && define('CONN_HOST_IP', $_SERVER['CONN_HOST_IP']);

\Motan\TestHelper::TestDefines();
// check server start
$connection = false;
$pid = pcntl_fork();
if ($pid > 0) {
    $count = 0;
    while (!$connection) {
        $connection = @stream_socket_client("tcp://127.0.0.1:9981");
        echo "sleep 1 second wait server start\n";
        sleep(1);
        $count++;
        if ($count > 10) {
            exit(0);
        }
    }
    @fclose($connection);
} elseif ($pid == 0){
    if ($connection) {
        return;
    }
    $spid = pcntl_fork();
    if ($spid > 0) {
        sleep(60);
        posix_kill($spid , SIGINT);
        sleep(1);
        exit(0);
    } elseif($spid == 0) {
        include_once __DIR__."/MockServer/BasicService.php";
        include_once __DIR__."/MockServer/Server.php";
        include_once __DIR__."/MockServer/Socket.php";
        include_once __DIR__ . "/MockServer/GrpcPbService.php";
        include_once __DIR__."/MockServer/BreezeService.php";
        include_once __DIR__."/MockServer/SimpleService.php";

        $server = new Server();
        pcntl_signal(SIGINT, function () {
            exit(0);
        });
        $server->listen();
    }
}