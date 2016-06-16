<?php

namespace Lschs\Sma\RestClient;

interface RestClient {

    /**
     * @param $id
     * @param $count
     * @param $keywords
     * @return mixed
     */
    public function get($id, $count, $keywords);
}