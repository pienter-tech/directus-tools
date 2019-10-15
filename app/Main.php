<?php

namespace DirectusTools;

use League\CLImate\CLImate;

class Main
{
    private $commands = ['Help', 'Update', 'Create'];
    /** @var CLImate */
    private $cli;
    /** @var string */
    private $cmd;
    /** @var bool */
    public $git = false;
    /** @var bool */
    public $help = false;
    /** @var string */
    public $root;
    /** @var string */
    public $name;

    public function __construct()
    {
        $this->cli = new CLImate();
        $this->getOptions();
        $this->cmd = $this->cli->arguments->get('command');
    }

    public function run()
    {
        foreach ($this->commands as $command) {
            $class = "DirectusTools\\Commands\\$command";
            if ($this->cmd === call_user_func("$class::name")) {
                $command = new $class($this->cli);
                $command();
                exit(0);
            }
        }
        $this->cli->error('Command not found. Use -h to get more info');
        exit(1);
    }

    private function getOptions()
    {
        $this->cli->description('Directus Tools aims to make developing with Directus CMS a pleasure');
        $this->cli->arguments->add([
            'command' => [
                'description' => 'The command you want to execute (create, update)',
                'defaultValue' => 'update',
            ],
        ]);
        foreach ($this->commands as $command) {
            $this->cli->arguments->add(call_user_func("DirectusTools\\Commands\\$command::arguments"));
        }
        $this->cli->arguments->parse();
    }
}
