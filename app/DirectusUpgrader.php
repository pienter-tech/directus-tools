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
    /** @var bool */
    public $dotenv = false;
    /** @var string */
    public $root;

    public function __construct() {
        $this->cmd = new Command();
        $this->setOptions();
        $this->verbose = $this->cmd['verbose'] ;
        $this->root = $this->cmd['root'];
        $this->git = $this->cmd['git'] || $this->cmd['all'];
        $this->dotenv = $this->cmd['env'] || $this->cmd['all'];
        $this->customComposer = $this->cmd['composer'] || $this->cmd['all'];
    }

    public function run() {
        if ($this->git) {
            $this->checkGitignore();
        }
        $this->repoClean();
        $this->clone();
        $this->cloneClean();
        $this->backupComposer();
        $this->moveNewDirectus();
        if ($this->dotenv) {
            $this->addDotenv();
        }
        if ($this->git) {
            $this->addToGit();
        }
        $this->info();
    }

    private function addDotenv() {
        if (!is_file("{$this->root}/src/web.php")) {
            $this->log('Web.php file not found', 'alert');
            return false;
        }

        $webContent = file("{$this->root}/src/web.php", FILE_IGNORE_NEW_LINES);
        $dotenvLine = Helpers::fuzzyArraySearch("Dotenv::create(\$basePath)", $webContent);
        if ($dotenvLine !== false) {
            $this->log('Dotenv already loaded', 'alert');
            return false;
        }
        $autoloadLine = Helpers::fuzzyArraySearch("require \$basePath . '/vendor/autoload.php'", $webContent);
        if ($autoloadLine === false) {
            $this->log('Autoload line not found', 'alert');
            return false;
        }
        $dotenvLines = ['$dotenv = Dotenv\Dotenv::create($basePath);', '$dotenv->load();'];
        array_splice($webContent, $autoloadLine + 1, 0, $dotenvLines);
        file_put_contents("{$this->root}/src/web.php", join("\n", $webContent));

    }

    private function checkGitignore() {
        if (!is_file("{$this->root}/.gitignore")) {
            $this->log('No .gitignore file found, create one? (y/n)', 'alert');
            $yOrN = strtolower(trim(fgets(STDIN)));
            if ($yOrN === 'y' || $yOrN === 'yes') {
                $this->log('Creating .gitignore file for you', 'info');
                $newGitignore = fopen("{$this->root}/.gitignore", 'w') or die('Could not create .gitignore');
                $newContent = "# Created by directus-upgrade script\nupgrade_directus\ncomposer.bckp.json";
                fwrite($newGitignore, $newContent);
                fclose($newGitignore);
            }
        }

        $gitignoreContent = file("{$this->root}/.gitignore", FILE_IGNORE_NEW_LINES);
        $upgradeDirectusIncluded = array_search('upgrade_directus', $gitignoreContent);
        $composerBckpIncluded = array_search('composer.bckp.json', $gitignoreContent);
        if ($upgradeDirectusIncluded === false || $composerBckpIncluded === false) {
            $this->log('Update .gitignore file? (y/n)', 'alert');
            $yOrN = strtolower(trim(fgets(STDIN)));
            if ($yOrN === 'y' || $yOrN === 'yes') {
                array_push($gitignoreContent, '# Added by directus-upgrade script');
                if ($upgradeDirectusIncluded === false) {
                    array_push($gitignoreContent, 'upgrade_directus');
                }
                if ($composerBckpIncluded === false) {
                    array_push($gitignoreContent, 'composer.bckp.json');
                }
                file_put_contents("{$this->root}/.gitignore", join("\n", $gitignoreContent));
            }
        }
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
        system("cp -a {$this->root}/upgrade_directus/. {$this->root}/");
    }

    private function addToGit() {
        system("git --git-dir {$this->root}/.git add .");
        $this->log('Added new files to git');
    }

    public function log($string, $type = 'normal') {
        switch ($type) {
            case 'normal':
                if ($this->verbose) {
                    echo Color::white() . $string . Color::reset() . PHP_EOL;
                }
                break;
            case 'alert':
                echo Color::red() . $string . Color::reset() . PHP_EOL;
                break;
            case 'info':
                echo Color::cyan() . $string . Color::reset() . PHP_EOL;
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

        $this->cmd->option('e')
            ->aka('env')
            ->aka('dotenv')
            ->describedAs('Update web.php file and load Dotenv\Dotenv (add Dotenv\Dorenv to composer.json or composer.custom.json)')
            ->boolean();

        $this->cmd->option('c')
            ->aka('composer')
            ->aka('custom_composer')
            ->describedAs('Merge composer.custom.json into composer.json')
            ->boolean();

        $this->cmd->option('a')
            ->aka('all')
            ->describedAs('Alias for -g -e -c')
            ->boolean();
    }
}
