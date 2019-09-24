<?php


namespace DirectusUpgrader;


class Helpers
{
    /**
     * @param string $needle
     * @param string[] $array
     * @return bool|int
     */
    static function fuzzyArraySearch($needle, $array) {
        foreach ($array as $index => $hay) {
            if(strpos($hay, $needle) !== false) {
                return $index;
            }
        }
        return false;
    }
}
