<?php
define('FF_VERSION', '0.1');
require 'vendor/autoload.php';

$client = new \phparsenal\fastforward\Client();
$client->run($argv);

