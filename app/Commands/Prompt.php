<?php

namespace DirectusTools\Commands;

use DirectusTools\Commands\Helpers\CloneCommands;
use DirectusTools\Commands\Helpers\GitIgnoreCommands;
use DirectusTools\Commands\Helpers\ComposerCommands;
use DirectusTools\Exceptions\FileException;
use DirectusTools\Exceptions\RunException;

class Prompt extends CommandClass
{
    use ComposerCommands, CloneCommands, GitIgnoreCommands;

    /** @var string */
    private $root;
    /** @var string */
    private $name;
    /** @var bool */
    private $composer;
    /** @var bool */
    private $quiet;
    /** @var bool */
    private $ignore;
    /** @var bool */
    private $updateComposer;

    /**
     * @return  string
     */
    static function name() {
        return 'prompt';
    }

    /**
     * Returns a safe filename, for a given platform (OS), by replacing all
     * dangerous characters with an underscore.
     *
     * @param string $dangerous_filename
     *
     * @return Boolean
     */
    protected function sanitizeFileName(string $dangerous_filename) {
        $dangerous_characters = array(" ", '"', "'", "&", "/", "\\", "?", "#");

        return str_replace($dangerous_characters, '_', $dangerous_filename);
    }

    protected function checkOptions() {
        $options = [
            'composer' => 'Merge Custom Composer?',
            'ignore' => 'Update .gitignore?',
            'updateComposer' => 'Update composer?'
        ];

        $input    = $this->cli->checkboxes('Select options:', $options);
        $optionResponse = $input->prompt();

        $this->composer = in_array('composer', $optionResponse);
        $this->ignore = in_array('ignore', $optionResponse);
        $this->updateComposer = in_array('updateComposer', $optionResponse);
    }

    /**
     * @return void
     * @throws FileException
     * @throws RunException
     */
    public function run() {
        $cmdInput = $this->cli->radio('What do you want to do?',
            [
                'update' => 'update existing project',
                'create' => 'create a new project'
            ]);
        $cmd = $cmdInput->prompt();

        if ($cmd === 'update') {
            $defaultRoot = getcwd();
            $rootInput = $this->cli->input("Root of project? ($defaultRoot)");
            $rootInput->defaultTo($defaultRoot);
            $this->root = $rootInput->prompt();
            $this->cli->info("Root: $this->root");

            $this->checkOptions();

            $this->updateDirectus("{$this->root}/upgrade_directus");

            if ($this->composer) {
                $this->mergeCustomComposer();
            }

            if ($this->ignore) {
                $this->updateOrCreateGitIgnore();
            }

            if ($this->updateComposer) {
                $this->composerUpdate();
            }

            exit(1);
        }

        $nameInput = $this->cli->input('What is your project called? (Directus)');
        $nameInput->defaultTo('Directus');
        $this->name = $nameInput->prompt();
        $defaultRoot = getcwd() . '/' . $this->sanitizeFileName($this->name);
        $this->cli->info("Project name: $this->name");

        $rootInput = $this->cli->input("Root of project? ($defaultRoot)");
        $rootInput->defaultTo($defaultRoot);
        $this->root = $rootInput->prompt();
        $this->cli->info("Root: $this->root");

        $this->updateDirectus($this->root, true);

        $customComposerContent = [
            'name' => $this->name,
            'description' => 'Project created by directus tools',
            'require' => [],
        ];

        $this->createCustomComposer($customComposerContent);
        $this->mergeCustomComposer();
        $this->composerInstall();

        $updateGitignore = $this->cli->confirm("Create .gitignore?");
        if ($updateGitignore->comfirmed()) {
            $this->updateOrCreateGitIgnore();
        }

        exit();
    }

    public function info() {
        $this->cli->info('Run composer update');
    }

    static function arguments() {
        return [];
    }

    public function setArguments($arguments) {
    }
}
