<?php

namespace Lschs\Sma\Middleware;

class ParseTimeline {

    /**
     * Typical $unparsedTimeline structure
     *
     *{
     *  [
     *      [standard data format]
     *      [standard data format [timestamp]]
     *      [...] [...] [...]
     *  ] <-- message source 1
     *  [ [standard data format] [standard data format] [...] [...] [...] ] <-- message source 2
     * ... ...
     * }
     */

    /**
     * ParseTimeline constructor.
     *
     */
    private function __construct() {
        //
    }

    public static function parse($unparsedTimeline, $order) {

        switch($order) {

            case "chronological" :
                return self::parseChronologically($unparsedTimeline); //earliest on top
                break;
            case "reverse_chronological" :
                return self::parseReverseChronologically($unparsedTimeline); //oldest on top

            default:
                return self::parseChronologically($unparsedTimeline);
        }
    }

    private static function parseChronologically($unparsedTimeline) {
        foreach($unparsedTimeline as &$up) {
                usort($up, function ($item1, $item2) {
                    return $item2['time'] <=> $item1['time'];
                }); //sort
        }
        return $unparsedTimeline;

    }

    private static function parseReverseChronologically($unparsedTimeline) {
        foreach($unparsedTimeline as &$up) {
            usort($up, function ($item1, $item2) {
                return $item1['time'] <=> $item2['time'];
            }); //sort
        }
        return $unparsedTimeline;
    }

}