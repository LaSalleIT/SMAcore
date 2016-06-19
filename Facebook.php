<?php

namespace Lschs\Sma\RestClient;

/**
 * Facebook REST client
 * @author Alex Fang
 */

use Lschs\Config;
use Lschs\Helpers;

class Facebook implements RestClient {

    public $facebookAccessToken;
    private $facebookClientId;
    private $facebookClientSecret;
    private $facebookApiBaseUrl;

    public function __construct() {
        $this->facebookClientId = Config::$facebookApplicationId;
        $this->facebookClientSecret = Config::$facebookApplicationSecret;
        $this->facebookAccessToken = $this->getFacebookAuth();
        $this->facebookApiBaseUrl = "https://graph.facebook.com/v2.6/";
    }

    /**
     * Get Facebook access token
     * @return bool true: good false: bad
     */
    private function getFacebookAuth() {
        $baseUrl = "https://graph.facebook.com/v2.6";
        $authUrl = $baseUrl . "/oauth/access_token?grant_type=client_credentials&client_id=" . $this->facebookClientId .
            "&client_secret=" . $this->facebookClientSecret;
        $getAuth = curl_init();
        //Get app token
        curl_setopt($getAuth, CURLOPT_URL, $authUrl);
        curl_setopt($getAuth, CURLOPT_RETURNTRANSFER, true);
        $authResults = json_decode(curl_exec($getAuth), true);
        curl_close($getAuth);

        return $authResults["access_token"];
    }

    /**
     * Returns a rendered Array of Facebook timeline data of a specified user
     * for the middleware to render
     * @param $id String Facebook user/page id
     * @param $count integer Number of timeline feeds you'd like to fetch
     * @return array of rendered Standard data for middleware to parse
     * @internal param array $keywords an array of search keywords
     */
    public function get($id, $count) {
        return $this->getFacebookPagePosts($this->getFacebookPageFeed($id, $count));
    }

    /**
     * Fetches the facebook page posts
     * @param $messageIds array of Strings the array of message Ids accordingly
     * @return array of decoded JSON data
     */
    public function getFacebookPagePosts($messageIds) {
        $pagePosts = []; //init
        $counter = 0;
        foreach ($messageIds as &$msgId) {
            $pagePosts[$counter] = $this->renderSingleFacebookPagePost($msgId["id"]);
            $counter++;
        }
        return $pagePosts;
    }

    /**
     * Fetch single Facebook Post
     * @param $postId Facebook post ID
     * @return mixed decoded JSON response
     */
    private function renderSingleFacebookPagePost($postId) {
        //Fetch main post
        $fetchMainPost = curl_init();
        curl_setopt($fetchMainPost, CURLOPT_URL, $this->facebookApiBaseUrl . $postId .
            "?fields=attachments,reactions.summary(total_count),comments.summary(total_count),message,created_time&summary=total_count&access_token=" .
            $this->facebookAccessToken);
        curl_setopt($fetchMainPost, CURLOPT_RETURNTRANSFER, true);
        $mpResult = json_decode(curl_exec($fetchMainPost), true);
        curl_close($fetchMainPost);

        $mediaResult = $mpResult['attachments'];
        $userReactionsCount = $mpResult['reactions']["summary"]["total_count"];
        $userCommentsCount = $mpResult['comments']["summary"]["total_count"];

        //Organize data
        $result = (!Helpers::e($mediaResult["data"][0]["media"]["image"]["src"])) ? [
            "time" => strtotime($mpResult["created_time"]),
            "text" => $mpResult["message"],
            "source" => "Facebook client",
            "url" => "https://facebook.com/" . $postId,
            "likes" => $userReactionsCount,
            "comments" => $userCommentsCount,
            "ifmediaexists" => true,
            "mediaurl" => $mediaResult["data"][0]["media"]["image"]["src"]] : [
            "time" => strtotime($mpResult["created_time"]),
            "text" => $mpResult["message"],
            "source" => "Facebook client",
            "url" => "https://facebook.com/" . $postId,
            "likes" => $userReactionsCount,
            "comments" => $userCommentsCount,
            "ifmediaexists" => false];
        return $result;
    }

    /**
     * Fetches the facebook page plaintext feed
     * @param $id String Facebook user/page Id
     * @param $count integer total amount of feeds you'd like to fetch
     * @return array of decoded JSON data
     * @internal param $keywords
     */
    public function getFacebookPageFeed($id, $count) {

        $finalResult = $this->curlGet("/" . $id . "/feed", $this->parseCount($count));
        $joinedResult = [];
        $jcount = 0;
        foreach ($finalResult as &$f) {
            foreach ($f['data'] as &$d) {
                $joinedResult[$jcount] = $d;
                $jcount++;
            }
        }
        return $joinedResult;

    }

    /**
     * Access Facebook Graph API endpoints using GET
     * @param $uri String the URI for the endpoint
     * @param $page array pagintation strategy
     * @return array (assoc) of results
     */
    public function curlGet($uri, $page) {
        $baseUrl = "https://graph.facebook.com/v2.6";
        $fetchUrl = $baseUrl . $uri;

        $finalResult = [];

        //Fetch URI content
        for ($i = 0; $i < $page[0]; $i++) {
            $limit = ($i == $page[0] - 1) ? $page[1] : 100; //get correct page limit
            $getContent = curl_init();
            if ($i == 0) { //0th iteration
                curl_setopt($getContent, CURLOPT_URL,
                    $fetchUrl . "?access_token=" . $this->facebookAccessToken . "&limit=" . $limit);
            } else { //$i-th iteration
                curl_setopt($getContent, CURLOPT_URL,
                    preg_replace('/limit\=(.*?)\&/', 'limit=' . $limit . '&', $finalResult[$i - 1]["paging"]["next"]));
            }
            curl_setopt($getContent, CURLOPT_RETURNTRANSFER, true);
            $result = json_decode(curl_exec($getContent), true);
            $finalResult[$i] = $result;
            curl_close($getContent);
        }

        return $finalResult;

    }

    /**
     * Parses $count and decides the best pagintation strategy
     * @param $count integer amount of timeline feeds
     * @return array [0] num of pages [1] number of feeds to fetch on the last
     * page
     */
    private function parseCount($count) {
        if ($count <= 100) {
            return [
                1,
                $count];
        } else { //more than 100
            return [
                (intdiv($count, 100) + 1),
                ($count - ((intdiv($count, 100) * 100) + 1) + 1)]; //get pagintation strategy and last page's feed count
        }
    }
}
