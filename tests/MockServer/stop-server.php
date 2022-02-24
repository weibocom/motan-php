<?php

$serverPid = exec("lsof -i :9981 | awk '{print $2}' | tail -n 1") . "\n";
exec("kill -9 $serverPid");