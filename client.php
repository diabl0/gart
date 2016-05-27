<?php
use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
$config = Yaml::parse(file_get_contents(__DIR__.'/config.yml'));

$client = new GearmanClient();
$client->addServers($config['gearman']['servers']);

for ($i = 0; $i < 60; $i++) {
    foreach ($config['google']['realtime']['dimmensions'] as $key => $dimmensions) {
        $w = $client->doBackground(
            'scrapGART',
            serialize(
                [
                    'dimmensions' => $dimmensions,
                    'collection'  => $key,
                ]
            )
        );
    }

    sleep(1);
}
