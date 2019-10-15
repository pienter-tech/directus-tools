<?php

namespace DirectusTools\Commands\Helpers;

use League\CLImate\CLImate;

/**
 * Trait CloneCommands
 * @package DirectusTools\Commands\Helpers
 *
 * @property string $root
 * @property CLImate $cli
 * @property string $cloneFolder
 */
trait CloneCommands
{
    /**
     * @param string $cloneFolder
     * @param bool $firstTime
     * @return bool
     */
    private function updateDirectus($cloneFolder, $firstTime = false)
    {
        $this->cloneFolder = $cloneFolder;

        return $this->clearCloneFolder() && $this->cloneLatestDirectus() && $this->cleanCloneFolder($firstTime) && $this->overwriteDirectus();
    }

    /**
     * @return bool
     */
    private function cloneLatestDirectus()
    {
        $this->cli->info("Clone repo to {$this->cloneFolder}");
        system("git clone https://github.com/directus/directus.git {$this->cloneFolder}");
        $this->cli->info("Cloned repo");
        return true;
    }

    /**
     * @return bool
     */
    private function clearCloneFolder()
    {
        if (!is_dir($this->cloneFolder)) {
            return false;
        }
        $this->cli->info('Delete upgrade_directus folder');
        system("rm -rf {$this->cloneFolder}");
        $this->cli->info("Removed {$this->cloneFolder}");
        return true;
    }

    /**
     * @param $firstTime
     * @return bool
     */
    private function cleanCloneFolder($firstTime)
    {
        system("rm -rf {$this->cloneFolder}/.git/");
        system("rm -rf {$this->cloneFolder}/.github/");
        system("rm {$this->cloneFolder}/LICENSE.md");
        system("rm {$this->cloneFolder}/README.md");
        if (!$firstTime) {
            system("rm -rf {$this->cloneFolder}/public/uploads/");
            system("rm -rf {$this->cloneFolder}/public/extensions/custom/");
            system("rm -rf {$this->cloneFolder}/logs/");
            system("rm -rf {$this->cloneFolder}/vendor/");
            system("rm {$this->cloneFolder}/public/admin/config.js");
            system("rm {$this->cloneFolder}/public/admin/style.css");
            system("rm {$this->cloneFolder}/public/admin/script.js");
            system("rm {$this->cloneFolder}/.gitignore");
        }
        $this->cli->info("Cleaned {$this->cloneFolder}");
        return true;
    }

    /**
     * @return bool
     */
    private function overwriteDirectus()
    {
        if ($this->cloneFolder === $this->root) {
            return false;
        }
        system("cp -a {$this->cloneFolder}/. {$this->root}/");
        $this->cli->info('Overwritten directus source');
        return true;
    }
}
