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

Run `docker-compose up -d` to create the [PHPFarm](https://github.com/splitbrain/docker-phpfarm) container.

PHP5.3
------
    php-5.3 vendor/bin/phpunit -d memory_limit=512M --colors --debug  --coverage-text

PHP5.4
------
    php-5.4 vendor/bin/phpunit -d memory_limit=512M --colors --debug  --coverage-text

PHP5.5
------
    php-5.5 vendor/bin/phpunit -d memory_limit=512M --colors --debug  --coverage-text

PHP5.6
------
    php-5.6 vendor/bin/phpunit -d memory_limit=512M --colors --debug  --coverage-text

PHP7.0
------
    php-7.0 vendor/bin/phpunit -d memory_limit=512M --colors --debug  --coverage-text

PHP7.1
------
    php-7.1 vendor/bin/phpunit -d memory_limit=512M --colors --debug  --coverage-text
