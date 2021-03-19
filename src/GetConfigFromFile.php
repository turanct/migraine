<?php

namespace Turanct\Migraine;

final class GetConfigFromFile implements GetConfig
{
    private $translation;
    private $currentWorkingDirectory;
    private $configFile;

    public function __construct(ConfigTranslation $translation, string $currentWorkingDirectory, string $configFile)
    {
        $this->translation = $translation;
        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->configFile = $configFile;
    }

    public function get(): Config
    {
        $configFile = "{$this->currentWorkingDirectory}/{$this->configFile}";

        if (!is_file($configFile)) {
            throw new CouldNotGenerateConfig();
        }

        $fileContents = file_get_contents($configFile);

        $config = $this->translation->translate($this->currentWorkingDirectory, $fileContents);

        return $config;
    }
}
