<?php

/**
 * Twitter REST Client
 * @author Alex Fang
 */

namespace Lschs\Sma\RestClient;

use Abraham\TwitterOAuth\TwitterOAuth;
use Lschs\Config;
use Lschs\Helpers;
use Lschs\Sma\Middleware\Search;

class Twitter implements RestClient {

    private $connection;
    private $content;

    public function __construct() {
        $access_token = Config::$twitterAccessToken;
        $access_token_secret = Config::$twitterAccessToken_secret;
        $this->connection =
            new TwitterOAuth(Config::$twitterConsumerKey, Config::$twitterConsumerKey_secret, $access_token,
                $access_token_secret);
        $this->content = $this->connection->get("account/verify_credentials");
    }

    /**
     * Returns a parsed assoc array for routing middleware to render
     * @param $id
     * @param $count String the amount of tweets in the user timeline
     * @param $keywords array of Strings search keywords
     * @return String Array of Strings result
     */
    public function get($id, $count, $keywords) {
        $parsedData = $this->getRawData($id, $count);
        //var_dump($parsedData);
        $unencodedCompleteData = [];
        $tweetCount = 0; //reset counter to use again
        foreach ($parsedData as &$s) {
            $unencodedRenderingData = (!Helpers::e($s['entities']['media'][0]['media_url_https'])) ? [
                "time" => strtotime($s['created_at']),
                "text" => $s['text'],
                "likes" => $s['favorite_count'],
                "comments" => $s['retweet_count'],
                "url" => "https://twitter.com/" . $s['user']['screen_name'] . "/status/" . $s['id_str'],
                "source" => urldecode($s['source']),
                "ifmediaexists" => true,
                "media_url" => $s['entities']['media'][0]['media_url_https']

            ] : [
                "time" => strtotime($s['created_at']),
                "text" => $s['text'],
                "likes" => $s['favorite_count'],
                "comments" => $s['retweet_count'],
                "url" => "https://twitter.com/" . $s['user']['screen_name'] . "/status/" . $s['id_str'],
                "source" => urldecode($s['source']),
                "ifmediaexists" => false,
            ];
            $unencodedCompleteData[$tweetCount] = $unencodedRenderingData;
            $tweetCount++;
        }
        return $unencodedCompleteData;
    }

    /**
     * Get unparsed Twitter timeline data in an assoc array
     * @param $id
     * @param $count String, the total amount of timeline data
     * @return array
     */
    public
    function getRawData($id, $count) {
        return $this->connection->get("statuses/user_timeline", [
            "user_id" => $id,
            "count" => $count,
            "exclude_replies" => false]);
    }

}
