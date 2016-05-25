<?php
/**
 * 
 */
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents('config.yml'));

//echo '<pre>'; print_r( $value ); echo '</pre>';exit();

$m = new MongoClient($config['mongo']['server']); // connect
$db = $m->selectDB($config['mongo']['database']);

$clientEmail = $config['google']['clientEmail'];
$privateKey = file_get_contents($config['google']['privateKey']);
$RT = new Luxoft\GA\RT($clientEmail, $privateKey, $config['google']['viewId']);

$dimmensions = [
    'rt:city',
    'rt:browserVersion',
    'rt:operatingSystemVersion',

    'rt:pagePath',
    'rt:campaign',
    'rt:source',
    'rt:medium',
];


$data = $RT->fetchData($dimmensions);

$ret = $db->{$config['mongo']['collection']}->insert(
    [
        'time' => (int)date('YmdHis'),
        'data' => $data['rows'],
    ]
);

echo '<pre>';
print_r($ret);
echo '</pre>';
