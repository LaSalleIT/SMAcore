<?php

namespace Lschs\Sma\Middleware;

use \Lschs\Config;
use \Lschs\Sma\Middleware\Render;
use \Redis;

class RedisUpdate {

    private $redisServer;
    private $redisServerPort;
    private $redisServerSecret;
    private $connection;

    public function __construct() {
        $this->redisServer = Config::$redisServerAddress;
        $this->redisServerPort = Config::$redisServerPort;
        $this->redisServerSecret = Config::$redisServerSecret;
        //init
        $this->connection = new Redis();
        $this->connection->connect($this->redisServer, $this->redisServerPort);
        $this->connection->auth($this->redisServerSecret);
        $this->connection->bgSave(); //save first
        
    }

    public function update($which, $ids, $count, $keywords) {
        $renderedData = $this->getRenderedData($which, $ids, $count, $keywords);
        $count = 0; //counter
        if(!empty($renderedData)) {
            $this->connection->del('smaParsedList', 0, -1);
            foreach ($renderedData as &$k) { //loop through $renderedData, each of the client
                foreach ($k as &$d) { //each of the clients' data
                    $this->connection->rPush('smaParsedList', json_encode($d)); //push as we traverse through
                    $count++;
                }
                $count = 0; //reset counter
            }
            return true;
        } else { return false; } //do not update anything
    }

    public function get() {
        return $this->connection->lRange('smaParsedList', 0, -1);
    }

    private function getRenderedData($which, $ids, $count, $keywords) {
        $r = new Render($which);
        return json_decode($r->get($ids, $count, $keywords), true);
    }

    public function __destruct() {
        $this->connection->bgSave(); //save again
        $this->connection->close();
    }
}