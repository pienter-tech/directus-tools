<?php

namespace DirectusTools\Commands;

use DirectusTools\Arguments\Common;
use DirectusTools\Commands\Helpers\CloneCommands;
use DirectusTools\Commands\Helpers\GitIgnoreCommands;
use DirectusTools\Commands\Helpers\ComposerCommands;
use DirectusTools\Exceptions\ArgumentNotFoundException;
use DirectusTools\Exceptions\FileException;
use DirectusTools\Exceptions\RunException;
use League\CLImate\Argument\Manager;

class Update extends CommandClass
{
    use ComposerCommands, CloneCommands, GitIgnoreCommands;
    /** @var string */
    private $root;
    /** @var bool */
    private $composer;
    /** @var bool */
    private $quiet;

    /**
     * @return  string
     */
    static function name()
    {
        return 'update';
    }

    /**
     * @return  array
     * @throws ArgumentNotFoundException
     */
    static function arguments()
    {
        return [
            'root' => Common::getArgument('root'),
            'composer' => Common::getArgument('composer'),
            'quiet' => Common::getArgument('quiet'),
        ];
    }

    /**
     * @param Manager $arguments
     * @return void
     */
    public function setArguments($arguments)
    {
        if (empty($arguments->get('root'))) {
            $this->root = getcwd();
        } else {
            $this->root = $arguments->get('root');
        }

        $this->composer = $arguments->get('composer');
        $this->quiet = $arguments->get('quiet');
    }

    /**
     * @return void
     * @throws RunException
     * @throws FileException
     */
    public function run()
    {
        $this->backupComposer();
        $this->updateDirectus("{$this->root}/upgrade_directus");
        if ($this->composer) {
            $this->mergeCustomComposer();
        }
        $this->info();
    }

    public function info()
    {
        $this->cli->info('Compare composer.json and composer.bckp.json');
        $this->cli->info('then run composer update');
    }
}
