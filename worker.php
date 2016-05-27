<?php
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents(__DIR__.'/config.yml'));

$cc = new Luxoft\Gearman\Workers\GART();

// Reverse Worker Code
$worker = new GearmanWorker();
$worker->addServers($config['gearman']['servers']);
$worker->addFunction(
    'scrapGART',
    [
        $cc,
        'scrap',
    ]
);

while ($worker->work()) {
    // do nothing, just wait for input
}
