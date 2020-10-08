<?php

namespace DirectusTools\Arguments;

use DirectusTools\Exceptions\ArgumentNotFoundException;

class Common
{
    private static $arguments = [
        'root' => [
            'prefix' => 'r',
            'longPrefix' => 'root',
            'description' => 'Set root (if empty current working directory will be used',
            'castTo' => 'string',
            'defaultValue' => '',
        ],
        'composer' => [
            'prefix' => 'c',
            'longPrefix' => 'composer',
            'description' => 'Merge composer.custom.json into composer.json or create composer.custom.json',
            'noValue' => true,
        ],
        'quiet' => [
            'prefix' => 'q',
            'longPrefix' => 'quiet',
            'description' => 'Answer yes to all prompts',
            'noValue' => true,
        ],
    ];

    /**
     * @param $name
     * @return array
     * @throws ArgumentNotFoundException
     */
    static function getArgument($name)
    {
        if (!array_key_exists($name, self::$arguments)) {
            throw new ArgumentNotFoundException($name);
        }
        return self::$arguments[$name];
    }
}
