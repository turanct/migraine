<?php

namespace Turanct\Migraine;

interface GetConfig
{
    /**
     * @throws CouldNotGenerateConfig
     *
     * @return Config
     */
    public function get(): Config;
}
