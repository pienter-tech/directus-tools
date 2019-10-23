<?php

namespace DirectusTools\Commands\Helpers;

trait FuzzyArraySearchCommand
{
    /**
     * @param string|array $needle
     * @param string[] $array
     * @return bool|int
     */
    private function fuzzyArraySearch($needle, $array)
    {
        foreach ($array as $index => $hay) {
            if(is_array($needle)){
                $allNeedlesFound = true;
                foreach ($needle as $partNeedle) {
                    if (strpos($hay, $partNeedle) === false) {
                        $allNeedlesFound = false;
                        break;
                    }
                }
                if($allNeedlesFound) {
                    return index;
                }
            } else {
                if (strpos($hay, $needle) !== false) {
                    return $index;
                }
            }
        }
        return false;
    }
}
