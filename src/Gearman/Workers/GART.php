<?php
namespace Luxoft\Gearman\Workers;

use Luxoft\GA\RT;
use Predis\Client;

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

    /**
     * @var Client
     */
    protected $redis;

    /**
     * GART constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->redis  = new Client($config['redis']['connection'], $config['redis']['options']);

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

        $viewId = $this->redis->zrange('viewsUsageCount', 0, 0);
        $viewId = $viewId[0];
        $userId = $this->redis->zrange('usersUsageCount', 0, 0);
        $userId = $userId[0];

        $userData = $this->config['google']['realtime']['users'][$userId];

        $clientEmail = $userData['clientEmail'];
        $privateKey  = file_get_contents(__DIR__.'/../../../'.$userData['privateKey']);

        $this->RT = new RT($clientEmail, $privateKey, $viewId);

        $RT = $this->RT;

        $data = $RT->fetchData($dimmensions);

        // Increase usage counters
        $this->redis->zincrby('viewsUsageCount', 1, $viewId);
        $this->redis->zincrby('usersUsageCount', 1, $userId);

        try {
            $this->DB->{$collection}->insert(
                [
                    'time' => $time,
                    'data' => $data['rows'],
                ]
            );

            echo "[{$time}] Hit {$collection} V: {$viewId} U: {$userId}\n";
        } catch (\MongoDuplicateKeyException $e) {
            // sleep and ignore
            echo "Skipping duplicated\n";

            return;
        } catch (\Google_Service_Exception $e) {
            if (strpos($e->getMessage(), '(403) Quota') !== false) {
                // Damn, quote exceed
                $this->redis->zadd('viewsUsageCount', [$viewId => 10000]);
            }
        }

    }
}
