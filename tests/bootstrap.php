<?php
require __DIR__.'/../vendor/autoload.php';

\Motan\TestHelper::TestDefines();

// @TODO testing for mesh panic, and using mesh snapshot
// this need run mesh in the same node with php client
// define('AGENT_RUN_PATH', '/run/weibo-mesh');

define('DEFAULT_TEST_URL', 'motan2://127.0.0.1:9981/com.weibo.HelloMTService?group=motan-demo-rpc');
