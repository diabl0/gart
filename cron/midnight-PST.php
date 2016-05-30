<?php
/**
 * cron/midnight-PST.php
 *
 * Run at midnight PST timezone
 * Resets all redis counters
 *
 */

require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

// lets make sure it's midnight PST
$date = new DateTime();
$date->setTimezone(new DateTimeZone('PST'));
if ($date->format('H') != '0') {
    echo "{$date->format('H')} is not midnight PST!!!\n";

    return;
}

$config = Yaml::parse(file_get_contents(__DIR__.'/../config.yml'));
$redis  = new Predis\Client($config['redis']['connection'], $config['redis']['options']);

$existing = $redis->zscan('viewsUsageCount', 0);
echo '<pre>viewsUsageCount: ';
print_r($existing);
echo '</pre>';
$existing = $redis->zscan('usersUsageCount', 0);
echo '<pre>usersUsageCount: ';
print_r($existing);
echo '</pre>';


$redis->del(['viewsUsageCount', 'usersUsageCount']);
foreach ($config['google']['realtime']['views'] as $view) {
    $redis->zincrby('viewsUsageCount', 0, $view);
}
foreach ($config['google']['realtime']['users'] as $userId => $user) {
    $redis->zincrby('usersUsageCount', 0, $userId);
}


$existing = $redis->zscan('viewsUsageCount', 0);
echo '<pre>viewsUsageCount: ';
print_r($existing);
echo '</pre>';
$existing = $redis->zscan('usersUsageCount', 0);
echo '<pre>usersUsageCount: ';
print_r($existing);
echo '</pre>';

echo "Done.\n";
