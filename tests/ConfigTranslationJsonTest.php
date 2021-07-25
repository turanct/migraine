<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class ConfigTranslationJsonTest extends TestCase
{
    public function testItFailsWhenNoCompleteConfigurationIsGiven()
    {
        $this->expectException(CouldNotGenerateConfig::class);

        $workingDirectory = __DIR__;

        $json = '
            {
                "directory": "migrations",
                "groups": {
                    "main": {
                        "user": "user",
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
                    new LogStrategyJson(__DIR__ . '/logs.json'),
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
                    new LogStrategyJson(__DIR__ . '/logs.json'),
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
                    new LogStrategyJson(__DIR__ . '/logs.json'),
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
            "two groups, user and password globally" => [
                '{
                    "directory": "migrations",
                    "groups": {
                        "user": "user1",
                        "password": "password1",
                        "main": {
                            "connection": "connection1"
                        },
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
                    new LogStrategyJson(__DIR__ . '/logs.json'),
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
                                new Database('connection2/shard1', 'user1', 'password1'),
                                new Database('connection2/shard2', 'user1', 'password1'),
                                new Database('connection3/shard3', 'user1', 'password1'),
                                new Database('connection3/shard4', 'user1', 'password1'),
                            ]
                        ),
                    ]
                )
            ],
            "two groups, user and password globally but overwritten" => [
                '{
                    "directory": "migrations",
                    "groups": {
                        "user": "user1",
                        "password": "password1",
                        "main": {
                            "user": "user2",
                            "password": "password2",
                            "connection": "connection1"
                        },
                        "shards": {
                            "shard1": {
                                "user": "user3",
                                "password": "password3",
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
                    new LogStrategyJson(__DIR__ . '/logs.json'),
                    [
                        new Group(
                            'main',
                            [
                                new Database('connection1', 'user2', 'password2'),
                            ]
                        ),
                        new Group(
                            'shards',
                            [
                                new Database('connection2/shard1', 'user3', 'password3'),
                                new Database('connection2/shard2', 'user1', 'password1'),
                                new Database('connection3/shard3', 'user1', 'password1'),
                                new Database('connection3/shard4', 'user1', 'password1'),
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
                    new LogStrategyJson(__DIR__ . '/logs.json'),
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
            "logging in file, different than the default" => [
                '{
                    "directory": "migrations",
                    "logs": {
                        "type": "file",
                        "file": "different-log-file.json"
                    },
                    "groups": {
                        "main": {
                            "connection": "connection1",
                            "user": "user1",
                            "password": "password1"
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    new LogStrategyJson(__DIR__ . '/different-log-file.json'),
                    [
                        new Group(
                            'main',
                            [
                                new Database('connection1', 'user1', 'password1'),
                            ]
                        ),
                    ]
                )
            ],
            "logging in database, connection only" => [
                '{
                    "directory": "migrations",
                    "logs": {
                        "type": "sql",
                        "connection": "connection1",
                        "table": "migraine"
                    },
                    "groups": {
                        "main": {
                            "connection": "connection1",
                            "user": "user1",
                            "password": "password1"
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    new LogStrategySQL('connection1', '', '', 'migraine'),
                    [
                        new Group(
                            'main',
                            [
                                new Database('connection1', 'user1', 'password1'),
                            ]
                        ),
                    ]
                )
            ],
            "logging in database, with user and password" => [
                '{
                    "directory": "migrations",
                    "logs": {
                        "type": "sql",
                        "connection": "connection1",
                        "table": "migraine",
                        "user": "user1",
                        "password": "password1"
                    },
                    "groups": {
                        "main": {
                            "connection": "connection1",
                            "user": "user1",
                            "password": "password1"
                        }
                    }
                }',
                new Config(
                    __DIR__,
                    'migrations',
                    new LogStrategySQL('connection1', 'user1', 'password1', 'migraine'),
                    [
                        new Group(
                            'main',
                            [
                                new Database('connection1', 'user1', 'password1'),
                            ]
                        ),
                    ]
                )
            ],
        ];
    }
}
