<?php

namespace DirectusTools\Commands;

use League\CLImate\Argument\Manager;

class Help extends CommandClass
{
    /** @var bool */
    private $help = false;

    /**
     * @return string
     */
    static function name()
    {
        return 'help';
    }

    /**
     * @return array
     */
    static function arguments()
    {
        return [
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => 'Show usage info',
                'noValue' => true,
            ],
        ];
    }

    /**
     * @param Manager $arguments
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->help = $arguments->get('help');
    }

    /**
     * @return void
     */
    public function run()
    {
        if ($this->help) {
            $this->cli->usage();
        } else {
            $this->cli->error('Command not found. Use -h to get more info');
        }
    }
}
