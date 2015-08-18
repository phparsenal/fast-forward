<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// Replace the path to *this* PHP file with the alias because the usage summary
// relies on this.
if ($argc > 0) {
    $argv[0] = "ff";
}

$client = new \phparsenal\fastforward\Client();
try {
    $client->init();
    $client->run($argv);
} catch (\Exception $e) {
    $msg = array($e->getMessage(), $e->getTraceAsString());
    $client->getOutput()->error($msg);
}
