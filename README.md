# 🤯 Migraine

[![Build Status][ico-travis]][link-travis]


A simple way of providing database migrations to your project


## Disclaimer

Use this package at your own risk.


## Goals

- [x] Write migrations as simple SQL statements
- [x] Be framework agnostic
- [x] Run migrations on multiple databases 
- [x] Run different migrations on different groups of databases
- [x] Allow seeding of the databases
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


### Prepare your migrations directory

```sh
mkdir migrations
mkdir migrations/main
mkdir migrations/shards
```


### Add some migrations

You can use the `new` command to create a new migration:

```sh
vendor/bin/migraine new main "create users tabel"
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

If you want to commit to the migration you just did a dry-run for, commit:

```sh
vendor/bin/migraine migrate --commit
```

If you want to only migrate a given group, specify it:

```sh
vendor/bin/migraine migrate --group shards --commit
```

If you want to only run a specific migration, specify it:

```sh
vendor/bin/migraine migrate --migration 20200426195959000-create-data-table.sql --commit
```


### Seeding

You can create seeds by adding a `/seeds` directory to a group's directory.
In that directory, you can add files just like any other migration.

```sh
mkdir main/seeds

touch main/seeds/20200426195623000-seed-users.sql
```

it will create this file:
`migrations/main/seeds/20200426195623000-seed-users.sql`


Fill it with the migration you want to run:
```sql
INSERT INTO `users` (`id`, `name`, `email`)
VALUES ('1', 'admin', 'admin@example.com');
```

If you want to apply a specific seed, specify it

```sh
vendor/bin/migraine seed --seed seeds/20200426195623000-seed-users
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.


## Security

If you discover any security related issues, please email spinnewebber_toon@hotmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-travis]: https://img.shields.io/travis/turanct/migraine/master.svg?style=flat-square
[link-travis]: https://travis-ci.org/turanct/migraine
