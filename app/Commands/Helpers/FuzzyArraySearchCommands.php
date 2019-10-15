<?php

namespace DirectusTools\Commands\Helpers;

trait FuzzyArraySearchCommands
{
    /**
     * @param string $needle
     * @param string[] $array
     * @return bool|int
     */
    private function fuzzyArraySearch($needle, $array)
    {
        foreach ($array as $index => $hay) {
            if (strpos($hay, $needle) !== false) {
                return $index;
            }
        }
        return false;
    }
}
