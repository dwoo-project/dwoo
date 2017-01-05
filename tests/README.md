Dwoo PHPUnit Tests
==================
The unit tests for Dwoo are implemented using the PHPUnit testing framework and require PHPUnit to run.

Installation
------------
PHPUnit is already declared inside the `require-dev` section of the **composer.json** file of this project.
The only thing you need to do is to run the next command, to install all packages listed, including `require-dev`:

	composer install

Running tests
-------------
To execute all tests, run the next command:

	vendor/bin/phpunit
	
To run a single test, you can execute a command like:

	vendor/bin/phpunit --filter testA tests/BlockTest
	
Dwoo PHPUnit Tests on multiple PHP versions using Docker
========================================================

https://github.com/splitbrain/docker-phpfarm

PHP5.3
------
```bash
php-5.3 phpunit -d memory_limit=512M --colors --debug  --coverage-text
```

PHP5.4
------
```bash
php-5.4 phpunit -d memory_limit=512M --colors --debug  --coverage-text
```

PHP5.5
------
```bash
php-5.5 phpunit -d memory_limit=512M --colors --debug  --coverage-text
```

PHP5.6
------
```bash
php-5.6 phpunit -d memory_limit=512M --colors --debug  --coverage-text
```

PHP7.0
------
```bash
php-7.0 phpunit -d memory_limit=512M --colors --debug  --coverage-text
```

PHP7.1
------
```bash
php-7.1 phpunit -d memory_limit=512M --colors --debug  --coverage-text
```
