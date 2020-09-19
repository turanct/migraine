<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class ConfigTranslationJsonTest extends TestCase
{
    /**
     * @expectedException \Turanct\Migraine\CouldNotGenerateConfig
     */
    public function testItFailsWhenNoCompleteConfigurationIsGiven()
    {
        $workingDirectory = __DIR__;

        $json = '
            {
                "directory": "migrations",
                "groups": {
                    "main": {
                        "user": "user"
                        "password": "password"
                    }
                }
            }
        ';

        $translation = new ConfigTranslationJson();
        $translation->translate($workingDirectory, $json);
    }

    /**
     * @dataProvider configTranslations
     */
    public function testItTranslatesJsonConfigToConfigObject(string $json, Config $config)
    {
        $workingDirectory = __DIR__;

        $translation = new ConfigTranslationJson();

        $this->assertEquals($config, $translation->translate($workingDirectory, $json));
    }

    public function configTranslations(): array
    {
        return [
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "connection": "connection",
                            "user": "",
                            "password": ""
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    [
                        new Group(
                            'main',
                            [new Database('connection', '', '')]
                        ),
                    ]
                )
            ],
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "connection": "connection",
                            "user": "user",
                            "password": "password"
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    [
                        new Group(
                            'main',
                            [new Database('connection', 'user', 'password')]
                        ),
                    ]
                )
            ],
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "connection": "connection1",
                            "user": "user",
                            "password": "password"
                        },
                        "shards": {
                            "user": "user",
                            "password": "password",
                            "shard1": {
                                "connection": "connection2/shard1"
                            },
                            "shard2": {
                                "connection": "connection2/shard2"
                            },
                            "shard3": {
                                "connection": "connection3/shard3"
                            },
                            "shard4": {
                                "connection": "connection3/shard4"
                            }
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    [
                        new Group(
                            'main',
                            [
                                new Database('connection1', 'user', 'password'),
                            ]
                        ),
                        new Group(
                            'shards',
                            [
                                new Database('connection2/shard1', 'user', 'password'),
                                new Database('connection2/shard2', 'user', 'password'),
                                new Database('connection3/shard3', 'user', 'password'),
                                new Database('connection3/shard4', 'user', 'password'),
                            ]
                        ),
                    ]
                )
            ],
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "shards": {
                            "shard1": {
                                "connection": "connection2/shard1"
                            },
                            "shard2": {
                                "connection": "connection2/shard2"
                            },
                            "shard3": {
                                "connection": "connection3/shard3"
                            },
                            "shard4": {
                                "connection": "connection3/shard4"
                            }
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    [
                        new Group(
                            'shards',
                            [
                                new Database('connection2/shard1', '', ''),
                                new Database('connection2/shard2', '', ''),
                                new Database('connection3/shard3', '', ''),
                                new Database('connection3/shard4', '', ''),
                            ]
                        ),
                    ]
                )
            ],
        ];
    }
}
