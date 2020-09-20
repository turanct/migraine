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
            "one group, empty user and password" => [
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
            "one group, filled user and password" => [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "connection": "connection",
                            "user": "user1",
                            "password": "password1"
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    [
                        new Group(
                            'main',
                            [new Database('connection', 'user1', 'password1')]
                        ),
                    ]
                )
            ],
            "two groups, user and password per group" => [
                '{
                    "directory": "migrations",
                    "groups": {
                        "main": {
                            "connection": "connection1",
                            "user": "user1",
                            "password": "password1"
                        },
                        "shards": {
                            "user": "user2",
                            "password": "password2",
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
                                new Database('connection1', 'user1', 'password1'),
                            ]
                        ),
                        new Group(
                            'shards',
                            [
                                new Database('connection2/shard1', 'user2', 'password2'),
                                new Database('connection2/shard2', 'user2', 'password2'),
                                new Database('connection3/shard3', 'user2', 'password2'),
                                new Database('connection3/shard4', 'user2', 'password2'),
                            ]
                        ),
                    ]
                )
            ],
            "one group, connection per group, no users or passwords" => [
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
