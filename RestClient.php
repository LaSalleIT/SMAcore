<?php

namespace Lschs\Sma\RestClient;

interface RestClient {

    /**
     * @param $id
     * @param $count
     * @return mixed
     * @internal param $keywords
     */
    public function get($id, $count);
}