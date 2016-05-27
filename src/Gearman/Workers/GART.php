<?php
namespace Luxoft\Gearman\Workers;

use Luxoft\GA\RT;
use Symfony\Component\Yaml\Yaml;

class GART
{
    /**
     * @var RT
     */
    protected $RT;
    /**
     * @var array
     */
    protected $config;
    /**
     * @var \MongoDB
     */
    protected $DB;
    /**
     * @var \GearmanClient
     */
    protected $gearmanClient;

    public function __construct()
    {
        $this->config = Yaml::parse(file_get_contents('config.yml'));
        $clientEmail  = $this->config['google']['clientEmail'];
        $privateKey   = file_get_contents($this->config['google']['privateKey']);

        $this->RT    = new RT($clientEmail, $privateKey, $this->config['google']['viewId']);
        $mongoClient = new \MongoClient($this->config['mongo']['server']); // connect
        $this->DB    = $mongoClient->selectDB($this->config['mongo']['database']);

        $this->gearmanClient = new \GearmanClient();
        $this->gearmanClient->addServers($this->config['gearman']['servers']);

    }

    public function scrap($job)
    {

        /* @var $job \GearmanJob */
        $time        = (int)date('YmdHis');
        $params      = unserialize($job->workload());
        $dimmensions = $params['dimmensions'];
        $collection  = $params['collection'];


        $result = $this->DB->{$collection}
            ->find([], ['time' => 1, '_id' => 0])
            ->sort(['time' => -1])
            ->limit(1);

        $lastTime = iterator_to_array($result)[0]['time'];

        if ($lastTime == $time) {
            sleep(2);

            $jobId = $this->gearmanClient->doBackground('scrap', serialize($params));
            echo "Requeued {$jobId}\n";

            return;
        }

        $RT = $this->RT;

        $data = $RT->fetchData($dimmensions);

        try {
            $this->DB->{$collection}->insert(
                [
                    'time' => $time,
                    'data' => $data['rows'],
                ]
            );

            echo "[{$time}] Hit {$collection} {$job->handle()}\n";
        } catch (\MongoDuplicateKeyException $e) {
            // sleep and ignore
            echo "Skipping duplicated\n";

            return;
        }

    }
}
