<?php
use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';
$config  = Yaml::parse(file_get_contents(__DIR__.'/config.yml'));
$gearman = new GearmanClient();
$redis   = new Predis\Client($config['redis']['connection'], $config['redis']['options']);

$gearman->addServers($config['gearman']['servers']);

// Prepare sorted list of usable views (add new, remove deleted)
$existing = $redis->zscan('viewsUsageCount', 0);

foreach ($config['google']['realtime']['views'] as $view) {
    $redis->zincrby('viewsUsageCount', 0, $view);
    unset($existing[1][$view]);
}
foreach ($existing[1] as $key => $val) {
    $redis->zrem('viewsUsageCount', $key);
}

// Prepare sorted list of usable users (add new, remove deleted)
$existing = $redis->zscan('usersUsageCount', 0);
foreach ($config['google']['realtime']['users'] as $userId => $user) {
    $redis->zincrby('usersUsageCount', 0, $userId);
    unset($existing[1][$userId]);
}
foreach ($existing[1] as $key => $val) {
    $redis->zrem('usersUsageCount', $key);
}

for ($i = 0; $i < 60; $i++) {
    foreach ($config['google']['realtime']['dimmensions'] as $key => $dimmensions) {
        $viewId = $redis->zrange('viewsUsageCount', 0, 0);
        $viewId = $viewId[0];
        $w      = $gearman->doBackground(
            'scrapGART',
            serialize(
                [
                    'dimmensions' => $dimmensions,
                    'collection'  => $key,
                ]
            )
        );
    }

    sleep(6 / $config['google']['realtime']['rps']);
}
