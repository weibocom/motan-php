<?php
// just for new phpt test for class method
require __DIR__.'/../vendor/autoload.php';

require __DIR__.'/../src/Motan/Client.php';
require __DIR__.'/../src/Motan/MClient.php';

require __DIR__.'/../src/Motan/Cluster/HaStrategy.php';
require __DIR__.'/../src/Motan/Cluster/Ha/Failfast.php';
require __DIR__.'/../src/Motan/Cluster/Ha/Failover.php';
require __DIR__.'/../src/Motan/Cluster/LoadBalance.php';
require __DIR__.'/../src/Motan/Cluster/LoadBalance/Random.php';
require __DIR__.'/../src/Motan/Cluster/LoadBalance/RoundRobin.php';
require __DIR__.'/../src/Motan/Cluster.php';
require __DIR__.'/../src/Motan/Constants.php';
require __DIR__.'/../src/Motan/Endpointer.php';
require __DIR__.'/../src/Motan/Endpoint/Agent.php';
require __DIR__.'/../src/Motan/Endpoint/Grpc.php';
require __DIR__.'/../src/Motan/Endpoint/Motan.php';
require __DIR__.'/../src/Motan/init.php';
require __DIR__.'/../src/Motan/Interfaces/RestyConfs.php';
require __DIR__.'/../src/Motan/Protocol/Header.php';
require __DIR__.'/../src/Motan/Protocol/Message.php';
require __DIR__.'/../src/Motan/Protocol/Motan.php';
require __DIR__.'/../src/Motan/Request.php';
require __DIR__.'/../src/Motan/Response.php';
require __DIR__.'/../src/Motan/Resty/Client.php';
require __DIR__.'/../src/Motan/Resty/Confs.php';
require __DIR__.'/../src/Motan/Serializer.php';
require __DIR__.'/../src/Motan/Serialize/GrpcJson.php';
require __DIR__.'/../src/Motan/Serialize/Motan.php';
require __DIR__.'/../src/Motan/Serialize/PB.php';
require __DIR__.'/../src/Motan/Transport/Connection.php';
require __DIR__.'/../src/Motan/URL.php';
require __DIR__.'/../src/Motan/Utils.php';
