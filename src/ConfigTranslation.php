<?php

namespace Turanct\Migrations;

interface ConfigTranslation
{
    /**
     * @throws CouldNotGenerateConfig
     */
    public function translate(string $json): Config;
}
