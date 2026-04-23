<?php
header('Content-Type: text/plain');
echo "ping ok " . date('H:i:s') . "\n";
echo "php " . PHP_VERSION . "\n";
echo "root: " . dirname(__DIR__) . "\n";
