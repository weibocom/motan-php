<?php
require __DIR__.'/../vendor/autoload.php';

if (isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME'] == 'php') {
    define('CONN_HOST_IP', '10.211.55.152');
}else {
    define('CONN_HOST_IP', '127.0.0.1');
    
}

if (isset($_SERVER['MESH_UP']) && $_SERVER['MESH_UP'] == 'yes'){
    define('MESH_CALL', TRUE);
    isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME'] == 'php' && define('D_AGENT_ADDR', CONN_HOST_IP . ':9981');
}else {
    define('D_CONN_DEBUG', CONN_HOST_IP . ':9100');
}

define('DEFAULT_TEST_URL', 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc');
