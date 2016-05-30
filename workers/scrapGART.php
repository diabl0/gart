<?php
/**
 *  GearmanManager worker
 *
 * @see https://github.com/brianlmoon/GearmanManager
 */

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

class scrapGART
{
    protected $GART;

    public function __construct()
    {
        $config     = Yaml::parse(file_get_contents(__DIR__.'/../config.yml'));
        $this->GART = new Luxoft\Gearman\Workers\GART($config);
    }

    public function run($job, &$log)
    {
        $this->GART->scrap($job);
        $log[] = "Success";
    }
}
