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
