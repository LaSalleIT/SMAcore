<?php

namespace Lschs\Sma\RestClient;

use \Lschs\Config;
use \Lschs\Helpers;

class Instagram implements RestClient {

    private $instagramAccessToken;
    private $instagramClientId;
    private $instagramClientSecret;
    private $instagramApiEndpoint;

    public function __construct() {
        $this->instagramAccessToken = Config::$instagramAccessToken;
        $this->instagramClientId = Config::$instagramClientId;
        $this->instagramClientSecret = Config::$instagramClientSecret;
        $this->instagramApiEndpoint = "https://api.instagram.com/v1/";
    }

    public function get($id, $count, $keywords) {
        return $this->render();
    }

    /**
     * @param $uri
     * @return array
     */
    private function curlGet($uri) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
            $this->instagramApiEndpoint . $uri . "?access_token=" . $this->instagramAccessToken);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $ret;
    }

    /**
     * @return mixed
     */
    private function getUnparsedInstagramTimeline() {
        return $this->curlGet("users/self/media/recent");
    }

    /**
     * @param $postId
     * @return int
     */
    private function getPostLikes($postId) {
        return count($this->curlGet("media/" . $postId . "/likes")["data"]);
    }

    /**
     * @param $postId
     * @return int
     */
    private function getPostComments($postId) {
        return count($this->curlGet("media/" . $postId . "/comments")["data"]);
    }

    private function render() {
        $timeline = $this->getUnparsedInstagramTimeline();
        $parsingData = [];
        $renderedData = [];
        $count = 0;
        foreach ($timeline['data'] as &$td) {
            $parsingData = [
                "time" => $td['created_time'],
                "text" => $td['caption']['text'],
                "source" => "Instagram client",
                "url" => $td['link'],
                "likes" => $td['likes']['count'],
                "comments" => $td['comments']['count'],
                "ifmediaexists" => true,
                "mediaurl" => (Helpers::e($td['videos']['standard_resolution']['url'])) ?
                    $td['images']['standard_resolution']['url'] :
                    $td['videos']['standard_resolution']['url']
            ];
            $renderedData[$count] = $parsingData;
            $count++;
        }
        return $renderedData;

    }
}