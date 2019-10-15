<?php

namespace DirectusTools\Commands\Helpers;

use DirectusTools\Exceptions\FileException;
use League\CLImate\CLImate;

/**
 * Trait GitIgnoreCommands
 * @package DirectusTools\Commands\Helpers
 *
 * @property string $root
 * @property CLImate $cli
 * @property bool $quiet
 */
trait GitIgnoreCommands
{
    /** @var string */
    private $root;
    /** @var CLImate */
    private $cli;
    /** @var bool */
    private $quiet;

    /**
     * @throws FileException
     */
    private function updateOrCreateGitIgnore()
    {
        if ($this->checkGitIgnoreExistance()) {
            $this->updateGitIgnore();
        }
    }

    /**
     * @return bool
     */
    private function updateGitIgnore()
    {
        $gitIgnoreContent = file("{$this->root}/.gitignore", FILE_IGNORE_NEW_LINES);
        $upgradeDirectusIncluded = array_search('upgrade_directus', $gitIgnoreContent);
        $composerBackupIncluded = array_search('composer.bckp.json', $gitIgnoreContent);
        if ($upgradeDirectusIncluded === false || $composerBackupIncluded === false) {
            $updateGitIgnore = $this->quiet || $this->cli->confirm('Update .gitignore file?')->confirmed();
            if (!$updateGitIgnore) {
                return false;
            }
            array_push($gitIgnoreContent, '# Added by directus-tools');
            if ($upgradeDirectusIncluded === false) {
                array_push($gitIgnoreContent, 'upgrade_directus');
            }
            if ($composerBackupIncluded === false) {
                array_push($gitIgnoreContent, 'composer.bckp.json');
            }
            file_put_contents("{$this->root}/.gitignore", join("\n", $gitIgnoreContent));
        }
        return true;
    }

    /**
     * @return bool
     * @throws FileException
     */
    private function checkGitIgnoreExistance()
    {
        if (!is_file("{$this->root}/.gitignore")) {
            $createGitIgnore = $this->quiet || $this->cli->confirm('No .gitignore file found, create one?')->confirmed();
            if ($createGitIgnore) {
                $this->cli->info('Creating .gitignore file for you');
                $newContent = "# Created by directus-upgrade script\nupgrade_directus\ncomposer.bckp.json";
                $this->createGitIgnore($newContent);
            }
            return false;
        }
        return true;
    }

    /**
     * @param string $content
     * @return bool
     * @throws FileException
     */
    private function createGitIgnore($content)
    {
        $newGitIgnore = fopen("{$this->root}/.gitignore", 'w');
        if ($newGitIgnore === false) {
            throw new FileException('gitIgnore');
        }
        fwrite($newGitIgnore, $content);
        fclose($newGitIgnore);
        return true;
    }
}
