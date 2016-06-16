<?php
/**
 * Created by PhpStorm.
 * User: explorer
 * Date: 6/13/16
 * Time: 9:11 AM
 */

namespace Lschs\Sma\Middleware;

use \Lschs\Sma\RestClient;

class Render {

    private $restClients;

    public function __construct($restClients) {
        $this->restClients = $restClients;
    }

    public function get($ids, $count, $keywords) {
       return json_encode(ParseTimeline::parse($this->getRestData($ids, $count, $keywords), ""));
    }

    private function getRestData($ids, $count, $keywords) {
        $results = [];
        for($i = 0; $i < count($this->restClients); $i++) {
            $rcName = '\\Lschs\\Sma\\RestClient\\' . $this->restClients[$i];
            $rc = new $rcName();
            $r = new \ReflectionObject(new $rcName);
            $results[$i] = $result = $r->getMethod('get')->invoke(new $rcName(), $ids[$i], $count, $keywords);
        }
        return $results;
    }

}