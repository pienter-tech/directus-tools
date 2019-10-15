<?php

namespace DirectusTools\Commands\Helpers;

use DirectusTools\Exceptions\FileException;
use DirectusTools\Exceptions\RunException;
use League\CLImate\CLImate;

/**
 * Trait ComposerCommands
 * @package DirectusTools\Commands\Helpers
 *
 * @property string $root
 * @property CLImate $cli
 * @property bool $quiet
 */
trait ComposerCommands
{
    /**
     * @return bool
     * @throws RunException
     * @throws FileException
     */
    private function mergeCustomComposer()
    {
        if ($this->checkComposer() && $this->checkCustomComposer()) {
            $composerJson = json_decode(file_get_contents("{$this->root}/composer.json"), true);
            $customComposerJson = json_decode(file_get_contents("{$this->root}/composer.custom.json"), true);
            $newComposerJson = array_merge_recursive($composerJson, $customComposerJson);
            $this->writeJson("{$this->root}/composer.json", $newComposerJson);
            $this->cli->info('Merged composer.custom.json');
        }
        return true;
    }

    /**
     * @param string $file
     * @param array $array
     * @return bool
     * @throws FileException
     */
    private function writeJson($file, $array)
    {
        $file = file_put_contents("$file", str_replace("\\", "\\\\", stripslashes(json_encode($array, JSON_PRETTY_PRINT))));
        if ($file === false) {
            throw new FileException($file);
        }
        return true;
    }

    /**
     * @return bool
     * @throws RunException
     */
    private function checkComposer()
    {
        if (!is_file("{$this->root}/composer.json")) {
            $this->cli->error('composer.json not found');
            throw new RunException('composer.json not found, project is not correctly setup');
        }
        return true;
    }

    /**
     * @return bool
     * @throws FileException
     */
    private function checkCustomComposer()
    {
        if (!is_file("{$this->root}/composer.custom.json")) {
            $createCustomComposer = $this->quiet || $this->cli->confirm('composer.custom.json not found, create now?')->confirmed();
            if (!$createCustomComposer) {
                return false;
            }
            $this->cli->info('Creating composer.custom.json for you');
            $newContent = ['keywords' => 'directus-tools'];
            $this->createCustomComposer($newContent);
        }

        return true;
    }

    /**
     * @param array $content
     * @return bool
     * @throws FileException
     */
    private function createCustomComposer($content)
    {
        $newCustomComposer = fopen("{$this->root}/composer.custom.json", 'w');
        fclose($newCustomComposer);
        $this->writeJson("{$this->root}/composer.custom.json", $content);
        return true;
    }

    private function backupComposer()
    {
        system("mv {$this->root}/composer.json {$this->root}/composer.bckp.json");
        $this->cli->info('Created composer.bckp.json');
    }
}
