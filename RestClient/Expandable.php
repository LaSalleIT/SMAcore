<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 6/18/16
 * Time: 6:49 PM
 */

namespace Lschs\Sma\RestClient;


use Lschs\Helpers;

/**
 * Class Expandable
 * @package Lschs\Sma\RestClient
 * @description S | U | P | E | R | H | E | R | O | I | C
 * Superheroic class, frees programmers out of job!!!!!!!!!!!!!!!!!!!!!
 */
class Expandable implements RestClient {

    private $queryEndpoint;
    private $apiKey;
    private $apiSecret;
    private $accessToken;

    /*
     * Access guideline
     * ----------------
     *
     * Structure:
     * {
     *   textLayerCount: [layerCount],
     *   text: [
     *      ...
     * }
     */
    private $accessGuideline;
    private $siteUrl;

    public function __construct($queryEndpoint, $apiKey, $apiSecret, $accessToken, $accessGuideline, $siteUrl) {
        $this->queryEndpoint = $queryEndpoint;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->accessToken = $accessToken;
        $this->accessGuideline = json_decode($accessGuideline, true);
        $this->siteUrl = $siteUrl;
    }

    public function get($id, $count) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->queryEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $listResult = curl_exec($ch);
        curl_close($ch);
        $finalResult = [];
        foreach ($listResult as $key => $result) {
            $finalResult[$key] = (Helpers::e($this->accessArray($result, $this->accessGuideline['mediaurl'],
                $this->accessGuideline['mediaurlLayerCount']))) ? [
                "time" => strtotime($this->accessArray($result, $this->accessGuideline['time'],
                    $this->accessGuideline['timeLayerCount'])),
                "text" => $this->accessArray($result, $this->accessGuideline['text'],
                    $this->accessGuideline['textLayerCount']),
                "source" => "La Salle College High School Social Media Manager",
                "url" => $this->siteUrl .
                    $this->accessArray($result, $this->accessGuideline['id'], $this->accessGuideline['idLayerCount']),
                "likes" => $this->accessArray($result, $this->accessGuideline['likes'],
                    $this->accessGuideline['likesLayerCount']),
                "comments" => $this->accessArray($result, $this->accessGuideline['comments'],
                    $this->accessGuideline['commentsLayerCount']),
                "ifmediaexists" => false] : [
                "time" => strtotime($this->accessArray($result, $this->accessGuideline['time'],
                    $this->accessGuideline['timeLayerCount'])),
                "text" => $this->accessArray($result, $this->accessGuideline['text'],
                    $this->accessGuideline['textLayerCount']),
                "source" => "La Salle College High School Social Media Manager",
                "url" => $this->siteUrl .
                    $this->accessArray($result, $this->accessGuideline['id'], $this->accessGuideline['idLayerCount']),
                "likes" => $this->accessArray($result, $this->accessGuideline['likes'],
                    $this->accessGuideline['likesLayerCount']),
                "comments" => $this->accessArray($result, $this->accessGuideline['comments'],
                    $this->accessGuideline['commentsLayerCount']),
                "ifmediaexists" => true,
                "mediaurl" => $this->accessArray($result, $this->accessGuideline['mediaurl'],
                    $this->accessGuideline['mediaurlLayerCount'])];
        }
        return $finalResult;
    }

    private function accessArray($array, $keys, $depth) {
        if ($depth > 1) {
            return $this->accessArray($array[$keys[count($keys) - ($depth)]], $keys, ($depth - 1));
        } else {
            return $array[$keys[(count($array) - 1)]];
        }
    }


}