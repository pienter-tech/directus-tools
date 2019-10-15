<?php

namespace DirectusTools\Commands;

use League\CLImate\Argument\Manager;
use League\CLImate\CLImate;

abstract class CommandClass
{
    /** @var CLImate */
    public $cli;

    /**
     * CommandInterface constructor.
     * @param CLImate $cli
     */
    public function __construct($cli) {
        $this->cli = $cli;
        $this->setArguments($cli->arguments);
    }

    public function __invoke() {
        $this->run();
    }

    /**
     * @return string
     */
    static abstract function name();

    /**
     * @return array
     */
    static abstract function arguments();

    /**
     * @param Manager $arguments
     * @return void
     */
    public abstract function setArguments($arguments);

    /**
     * @return void
     */
    public abstract function run();
}
