<?php

namespace Lschs\Sma\Middleware;

/**
 * Search.php vague gradient search implementation
 * Provides searching methods
 * @author Alex Fang
 */

class Search {

    private function __construct() {
        //
    }

    /**
     * Search whether the destination array contains a given array of keywords
     * @param $keywords array of Strings, the 1D array of given keywords
     * @param $destination String to be searched in
     * @return bool true if match is positive ( > 75% )
     */

    public static function keywordSearch($keywords, $destination) {
        $judgementSequence = array_fill(0, count($keywords), false);
        $iterationCount = 0;
        $judgementScale = [
            0,
            count($keywords)]; //fraction
        foreach ($keywords as &$k) {
            if (preg_match('[' . strtolower($k) . ']', strtolower($destination))) {
                $judgementSequence[$iterationCount] = true;
            }
            $iterationCount++;
        }
        foreach ($judgementSequence as &$jsq) {
            if ($jsq) {
                $judgementScale[0]++;
            }
        }
        return (($judgementScale[0] / $judgementScale[1]) >= 0.75);
    }
}
