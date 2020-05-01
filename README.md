# Migrations

[![Build Status][ico-travis]][link-travis]


A simple way of providing database migrations to your project


## Usage


### Install using Composer

```sh
composer require "turanct/migrations"
```

### Provide a config file

In this example we have two **groups**, `main` which is the main database, and `shards` which is a group of sharded databases. The difference between `main` and `shards` (in production) is that `shards` contains of multiple databases which all need to look the same. This means that if we do a migration, we'll run it on all those databases.

`migrations.json`

```json
{
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
}
```


### Prepare your migrations directory

```sh
mkdir migrations
mkdir migrations/main
mkdir migrations/shards
```


### Add some migrations

`migrations/main/20200426195623000-create-users-table.sql`

```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

`migrations/shards/20200426195959000-create-data-table.sql`

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
vendor/bin/migrate
```

If you want to commit to the migration you just did a dry-run for, commit:

```sh
vendor/bin/migrate --commit
```

If you want to only migrate a given group, specify it:

```sh
vendor/bin/migrate --group shards --commit
```

If you want to only run a specific migration, specify it:

```sh
vendor/bin/migrate --migration shards/20200426195959000-create-data-table.sql --commit
```



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.


## Security

If you discover any security related issues, please email spinnewebber_toon@hotmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-travis]: https://img.shields.io/travis/turanct/migrations/master.svg?style=flat-square
[link-travis]: https://travis-ci.org/turanct/migrations
