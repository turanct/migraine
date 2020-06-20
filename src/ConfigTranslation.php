<?php

namespace Turanct\Migrations;

interface ConfigTranslation
{
    /**
     * @throws CouldNotGenerateConfig
     */
    public function translate(string $workingDirectory, string $json): Config;
}
