# 🤯 Migraine

[![Build Status][ico-travis]][link-travis]
[![PHPUnit tests](https://github.com/turanct/migraine/actions/workflows/tests.yaml/badge.svg)](https://github.com/turanct/migraine/actions/workflows/tests.yaml)
[![Linters](https://github.com/turanct/migraine/actions/workflows/psalm.yaml/badge.svg)](https://github.com/turanct/migraine/actions/workflows/psalm.yaml)


A simple way of providing database migrations to your project


## Disclaimer

Use this package at your own risk.


## Goals

- [x] Write migrations as simple SQL statements
- [x] Be framework agnostic
- [x] Run migrations on multiple databases 
- [x] Run different migrations on different groups of databases
- [x] Allow seeding of the databases
- [x] Keep logs in SQL itself
- [ ] Have the ability to roll back migrations


## Usage


### Install using Composer

```sh
composer require "turanct/migraine"
```

### Provide a config file

In this example we have two **groups**, `main` which is the main database, and `shards` which is a group of sharded databases. The difference between `main` and `shards` (in production) is that `shards` contains of multiple databases which all need to look the same. This means that if we do a migration, we'll run it on all those databases.

`migrations.json`

```json
{
    "directory": "migrations",
    "groups": {
        "main": {
            "connection": "mysql:host=127.0.0.1;port=3306;dbname=main",
            "user": "user",
            "password": "password"
        },
        "shards": {
            "user": "user",
            "password": "password",
            "shard1": {
                "connection": "mysql:host=127.0.0.1;port=3306;dbname=shard1"
            },
            "shard2": {
                "connection": "mysql:host=127.0.0.1;port=3306;dbname=shard2"
            },
            "shard3": {
                "connection": "mysql:host=127.0.0.1;port=3306;dbname=shard3"
            },
            "shard4": {
                "connection": "mysql:host=127.0.0.1;port=3306;dbname=shard4"
            }
        }
    }
}
```

By default, migraine will log the migrations that are done in `logs.json` in your working directory, however this is configurable:

#### Logging in a different file

`migrations.json`

```json
{
    "directory": "migrations",
    "logs": {
        "type": "json",
        "file": "alternative-file.json"
    },
    "groups": {
        ...
    }
}
```

#### Logging in SQL

`migrations.json`

```json
{
    "directory": "migrations",
    "logs": {
        "type": "sql",
        "connection": "mysql:host=127.0.0.1;port=3306;dbname=main",
        "user": "user",
        "password": "password",
        "table": "logs"
    },
    "groups": {
        ...
    }
}
```


### Prepare your migrations directory

```sh
mkdir migrations
mkdir migrations/main
mkdir migrations/shards
```


### Add some migrations

You can use the `new migration` command to create a new migration:

```sh
vendor/bin/migraine new migration main "create users tabel"
```

it will create this file:
`migrations/main/20200426195623000-create-users-table.sql`


We'll fill it with the migration we want to run:
```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```


and this command for the `shards`:

```sh
vendor/bin/migraine new shards "create data tabel"
```

will create this file:
`migrations/shards/20200426195959000-create-data-table.sql`


We'll fill it with the migration we want to run:
```sql
CREATE TABLE IF NOT EXISTS `data` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `data` text,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```


### Run the migrations

This is how you run all migrations. We'll automatically dry-run your migration:

```sh
vendor/bin/migraine migrate
```

Or a dry-run with the seeds

```sh
vendor/bin/migraine migrate --seed
```

If you want to commit to the migration you just did a dry-run for, commit:

```sh
vendor/bin/migraine migrate --commit
```

If you want to commit to the migration & seeding you just did a dry-run for, commit:

```sh
vendor/bin/migraine migrate --seed --commit
```

If you want to only migrate a given group, specify it:

```sh
vendor/bin/migraine migrate --group shards --commit
```

If you want to only run a specific migration, specify it:

```sh
vendor/bin/migraine migrate --migration 20200426195959000-create-data-table.sql --commit
```

If you want to skip a migration (e.g. because you know it was already done manually):

```sh
vendor/bin/migraine skip --migration 20200426195959000-create-data-table.sql --commit
```

Use the skip functionality with caution. It writes a skipped log to the log file, and will never run this migration again, just like the migration was actually executed.


### Seeding

You can create seeds by adding a `/seeds` directory to a group's directory.
In that directory, you can add files just like any other migration.

You can use the `new seed` command to create a new seed:

```sh
vendor/bin/migraine new seed main "seed users"
```

It will create this file:
`migrations/main/seeds/20200426195623000-seed-users.sql`


Fill it with the seeding you want to run:
```sql
INSERT INTO `users` (`id`, `name`, `email`)
VALUES ('1', 'admin', 'admin@example.com');
```

If you want to apply a specific seed, specify it

```sh
vendor/bin/migraine seed --seed seeds/20200426195623000-seed-users
```


If you want to commit to the migration & seeding you just did a dry-run for, commit:

```sh
vendor/bin/migraine migrate --seed --commit
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.


## Security

If you discover any security related issues, please email spinnewebber_toon@hotmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-travis]: https://img.shields.io/travis/turanct/migraine/master.svg?style=flat-square
[link-travis]: https://travis-ci.org/turanct/migraine
