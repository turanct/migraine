<?php

namespace Turanct\Migrations;

use PHPUnit\Framework\TestCase;

final class ConfigTranslationJsonTest extends TestCase
{
    /**
     * @expectedException CouldNotGenerateConfig
     */
    public function testItFailsWhenNoCompleteConfigurationIsGiven()
    {
        $json = '
            {
                "directory": "migrations",
                "groups": {
                    "main": {
                        "host": "host",
                        "user": "user",
                        "password": "password"
                    }
                }
            }
        ';

        $translation = new ConfigTranslationJson();
        $translation->translate($json);
    }

    /**
     * @dataProvider configTranslations
     */
    public function testItTranslatesJsonConfigToConfigObject(string $json, Config $config)
    {
        $translation = new ConfigTranslationJson();

        $this->assertEquals($config, $translation->translate($json));
    }

    public function configTranslations(): array
    {
        return [
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "host": "host",
                            "user": "user",
                            "password": "password",
                            "database": "main"
                        }
                    }
                }',
                new Config(
                    'migrations',
                    [
                        new Group(
                            'main',
                            [new Database('host', 'user', 'password', 'main')]
                        ),
                    ]
                )
            ],
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "host": "host",
                            "user": "user",
                            "password": "password",
                            "shard1": {
                                "database": "main"
                            },
                            "shard2": {
                                "database": "backup"
                            }
                        }
                    }
                }',
                new Config(
                    'migrations',
                    [
                        new Group(
                            'main',
                            [
                                new Database('host', 'user', 'password', 'main'),
                                new Database('host', 'user', 'password', 'backup'),
                            ]
                        ),
                    ]
                )
            ],
            [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "host": "host1",
                            "user": "user",
                            "password": "password",
                            "database": "main"
                        },
                        "shards": {
                            "user": "user",
                            "password": "password",
                            "shard1": {
                                "host": "host2",
                                "database": "shard1"
                            },
                            "shard2": {
                                "host": "host2",
                                "database": "shard2"
                            },
                            "shard3": {
                                "host": "host3",
                                "database": "shard3"
                            },
                            "shard4": {
                                "host": "host3",
                                "database": "shard4"
                            }
                        }
                    }
                }',
                new Config(
                    'migrations',
                    [
                        new Group(
                            'main',
                            [
                                new Database('host1', 'user', 'password', 'main'),
                            ]
                        ),
                        new Group(
                            'shards',
                            [
                                new Database('host2', 'user', 'password', 'shard1'),
                                new Database('host2', 'user', 'password', 'shard2'),
                                new Database('host3', 'user', 'password', 'shard3'),
                                new Database('host3', 'user', 'password', 'shard4'),
                            ]
                        ),
                    ]
                )
            ],
        ];
    }
}
