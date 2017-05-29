TOML parser and builder for PHP
===============================

**This is very much WIP.** Do not use in production, maybe don't even call it a TOML parser just yet ;-)

## Install using composer

Open a shell, `cd` to your poject and type:

```sh
composer require malc0mn/php-toml-processor dev-master
```

or edit composer.json and add:

```json
{
    "require": {
        "malc0mn/php-toml-processor": "~1.0"
    }
}
```

## Usage examples

###### Parse file to array

```php
$config = Toml::fromFile('config.toml')->toArray();
```

###### PrettyPrint

```php
$toml = Toml::fromFile('config.toml')->prettyPrint(-1);

// Or like this
$toml = (string)Toml::fromFile('config.toml');
```


###### Edit a toml file and save it again

```php
$toml = Toml::fromFile('/path/to/config.toml');

// Do stuff with $toml... Some fancy helper methods will be added soon.

// Or like this
$toml->saveToFile('/path/to/config.toml');
```

## Credits

The basic setup is based on Roman Piták's [Nginx Config Processor](https://github.com/romanpitak/Nginx-Config-Processor)
which I personally like very much! Thanks for that @romanpitak! Credits were
left in the files I re-used and modified.

I also used the [Doctrine Lexer](https://github.com/doctrine/lexer) class which
I simply included as I did not want to add a dependency for a single file. I
also added an additional method (IMHO: a mistake in the Doctrine Lexer is a
missing setter for the `position` property. Not for the faint of heart, but it
would make the lexer completely extendable)...
Thanks to @doctrine for that one!

This repo was created by [integr.io](http://integr.io/) for use in one of
our Symfony applications.
It has been reworked with extensibility and ease of use in mind.
