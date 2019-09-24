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
    /** @var bool */
    public $git = false;
    /** @var string */
    public $root;

    public function __construct() {
        $this->cmd = new Command();
        $this->setOptions();
        $this->verbose = $this->cmd['verbose'];
        $this->root = $this->cmd['root'];
        $this->git = $this->cmd['git'];
    }

    public function run() {
        $this->repoClean();
        $this->clone();
        $this->cloneClean();
        $this->backupComposer();
        $this->moveNewDirectus();
        $this->repoClean();
        $this->addToGit();
        $this->info();
    }

    private function repoClean() {
        if (is_file($this->root . '/composer.bckp.json')) {
            $this->log('Delete composer.bckp.json');
            unlink($this->root . '/composer.bckp.json');
        }

        if (is_dir($this->root . '/upgrade_directus')) {
            $this->log('Delete upgrade_directus folder');
            system('rm -rf ' . $this->root . '/upgrade_directus/');
        }
    }

    private function clone() {
        $this->log('Clone repo');
        $quiet = $this->verbose ? '' : '-q';
        system("git clone {$quiet} https://github.com/directus/directus.git {$this->root}/upgrade_directus");
//        $tags = [];
//        exec('git ls-remote --tags https://github.com/directus/directus.git', $tags);
//        print_r($tags);
    }

    private function cloneClean() {
        system('rm -rf ' . $this->root . '/upgrade_directus/.git/');
        system('rm -rf ' . $this->root . '/upgrade_directus/.github/');
        system('rm -rf ' . $this->root . '/upgrade_directus/public/uploads/');
        system('rm -rf ' . $this->root . '/upgrade_directus/public/extensions/custom/');
        system('rm -rf ' . $this->root . '/upgrade_directus/logs/');
        system('rm -rf ' . $this->root . '/upgrade_directus/vendor/');
        system('rm ' . $this->root . '/upgrade_directus/public/admin/config.js');
        system('rm ' . $this->root . '/upgrade_directus/public/admin/style.css');
        system('rm ' . $this->root . '/upgrade_directus/public/admin/script.js');
        system('rm ' . $this->root . '/upgrade_directus/.gitignore');
        system('rm ' . $this->root . '/upgrade_directus/LICENSE.md');
        system('rm ' . $this->root . '/upgrade_directus/README.md');
    }

    private function backupComposer() {
        system("mv {$this->root}/composer.json {$this->root}/composer.bckp.json");
    }

    private function moveNewDirectus() {
        system("cp -r {$this->root}/upgrade_directus/ {$this->root}/");
    }

    private function addToGit() {
        if($this->git) {
            system("git --git-dir {$this->root}/.git add .");
            $this->log('Added new files to git');
        }
    }

    public function log($string, $type = 'normal') {
        switch ($type) {
            case 'normal':
                if($this->verbose){
                    echo Color::white() . $string . Color::reset() . PHP_EOL;
                }
                break;
            case 'alert':
                echo Color::red() . $string . Color::reset() . PHP_EOL;
                break;
            case 'info':
                echo Color::bold_blue() . $string . Color::reset() . PHP_EOL;
                break;
        }
    }

    public function info() {
        $this->log('Compare composer.json and composer.bckp.json', 'info');
        $this->log('then run composer update', 'info');

    }

    private function setOptions() {
        $this->cmd->option('v')
            ->aka('verbose')
            ->describedAs('Be more verbose please')
            ->boolean();

        $this->cmd->option('r')
            ->aka('root')
            ->describedAs('Set root (if empty current working directory will be used')
            ->defaultsTo(getcwd());

        $this->cmd->option('g')
            ->aka('git')
            ->describedAs('Add new files to git')
            ->boolean();
    }
}
