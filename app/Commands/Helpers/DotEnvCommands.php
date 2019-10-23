<?php

namespace DirectusTools\Commands\Helpers;

use DirectusTools\Exceptions\RunException;
use League\CLImate\CLImate;

/**
 * Trait DotEnvCommands
 * @package DirectusTools\Commands\Helpers
 *
 * @property string $root
 * @property CLImate $cli
 * @property int $autoLoadLine
 */
trait DotEnvCommands
{
    use FuzzyArraySearchCommand;

    private $files = [
        '/src/web.php',
        '/bin/directus',
        '/public/downloads/index.php',
        '/public/thumbnail/index.php'
    ];

    /**
     * @return bool
     * @throws RunException
     */
    private function addDotenv()
    {
        foreach($this->files as $file) {
            if (!is_file("{$this->root}$file")) {
                throw new RunException("$file not found, project is not correctly setup");
            }

            $webContent = file("{$this->root}$file", FILE_IGNORE_NEW_LINES);
            if (!($this->hasExistingDotenv($webContent) && $this->hasAutoloadLine($webContent))) {
                return false;
            }
            $dotEnvLines = ['$dotenv = Dotenv\Dotenv::create($basePath);', '$dotenv->load();'];
            array_splice($webContent, $this->autoLoadLine + 1, 0, $dotEnvLines);
            file_put_contents("{$this->root}$file", join("\n", $webContent));
            $this->cli->info("Enabled dotenv in $file");
        }

        return true;
    }

    private function hasExistingDotenv($webContent)
    {
        $dotEnvLine = $this->fuzzyArraySearch("Dotenv::create(\$basePath)", $webContent);
        if ($dotEnvLine !== false) {
            $this->cli->error('Dotenv already loaded');
            return false;
        }
        return true;
    }

    /**
     * @param $webContent
     * @return bool
     * @throws RunException
     */
    private function hasAutoloadLine($webContent)
    {
        $autoloadLine = $this->fuzzyArraySearch(["require","vendor/autoload.php"], $webContent);
        if ($autoloadLine === false) {
            throw new RunException('Autoload line not found');
        }
        $this->autoLoadLine = $autoloadLine;
        return true;
    }
}
