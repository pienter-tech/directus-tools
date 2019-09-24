<?php


namespace DirectusUpgrader;


use Codedungeon\PHPCliColors\Color;
use Commando\Command;

class DirectusUpgrader
{
    /** @var Command */
    private $cmd;
    /** @var bool */
    public $verbose = false;
    /** @var string */
    public $root;

    public function __construct() {
        $this->cmd = new Command();
        $this->verbose = $this->cmd['verbose'];
        $this->root = $this->cmd['root'];
    }

    public function echo($string, $type = 'normal') {
        switch ($type) {
            case 'normal':
                echo Color::white() . $string . Color::reset() . PHP_EOL;
                break;
            case 'alert':
                echo Color::red() . $string . Color::reset() . PHP_EOL;
                break;
        }
    }

    private function setOptions() {
        $this->cmd->option('v')
            ->aka('verbose')
            ->describedAs('Be more verbose please')
            ->boolean();

        $this->cmd->option('r')
            ->aka('root')
            ->map(function($value){
                if ($value) {
                    return rtrim($value, '/');
                } else {
                    return getcwd();
                }
            });
    }
}
