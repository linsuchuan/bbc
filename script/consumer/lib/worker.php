<?php
//-----------------------------------------------------------------------------------------------
//init ec-os framework start;
require_once(__DIr__ .'/../../lib/runtime.php');

//-----------------------------------------------------------------------------------------------
$topic = $_SERVER['argv'][1];
worker::checkTopic($topic);

$worker = new worker($topic);

$worker->run();

class worker{

    private $maxRunTimes = 0;
    private $runTimes = 0;
    private $queueServer = null;
    private $topic = '';

    public function __construct($topic)
    {

        $this->topic = $topic;
        $this->queueServer = system_queue::instance();

        $consumeConfig = $this->getConfigInfo($topic);
        $this->maxRunTimes = $consumeConfig['maxRunTimes'];

        logger::debug("init worker with: topic-$topic");
    }

    public function run()
    {
        $startTime = microtime();

        while($this->runTimes < $this->maxRunTimes)
        {
            $topic = $this->topic;
            logger::debug( "run worker $topic, times : $this->runTimes.");
            $qs = $this->queueServer;
            try{
                if ($queueMessgage = $qs->get($topic)) {
                    $qs->consumer($queueMessgage);
                }
                else
                {
                    sleep(1);
                }
            }catch(Exception $e){
                $msg = $e->getMessage();
                logger::error(json_encode(['msg'=>$msg]));
                logger::debug(json_encode(['msg'=>$msg, 'exception'=>$e->__toString()]));
            }
            $this->runTimes++ ;
        }

        $endTime = microtime();

        logger::debug('The worker start at "' . $startTime . '", end at "' . $endTime . '", use "' . $endTime - $startTime . 's".');

    }

    public function getConfigInfo()
    {
        $config = config::get('queue.consume');

        return $config;
    }

    public static function checkTopic($topic)
    {
        if(empty($topic))
            throw new RuntimeException('Topic is not defined !!!');

        if(is_null(config::get('queue.queues.'.$topic)))
            throw new RuntimeException('Cannot find the queue which topic is "' . $topic . '"');

    }

}

