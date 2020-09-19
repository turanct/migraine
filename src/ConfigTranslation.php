<?php

namespace Turanct\Migraine;

interface ConfigTranslation
{
    /**
     * @throws CouldNotGenerateConfig
     */
    public function translate(string $workingDirectory, string $json): Config;
}
