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

	vendor/bin/phpunit --filter testA tests/Blocktest