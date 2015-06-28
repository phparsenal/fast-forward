<?php
require 'vendor/autoload.php';

$client = new \phparsenal\fastforward\Client();
try {
    $client->init();
    $client->run($argv);
} catch (\Exception $e) {
    $client->getCLI()
        ->br()
        ->error('<bold>' . $e->getMessage() . '</bold>')
        ->error($e->getTraceAsString());
}
