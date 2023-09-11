<?php

ob_start();

$start = $_SERVER['HTTP_X_START_TIME'];
$end = microtime(true);

echo "Request took " . round(($end - $start) * 1000, 2) . " milliseconds.\n";
