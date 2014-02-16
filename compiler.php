<?php
try {
	$pharFile = 'dwoo.phar';
	if (file_exists($pharFile)) {
		unlink($pharFile);
	}

	$phar = new Phar($pharFile, FilesystemIterator::CURRENT_AS_FILEINFO);
	$phar->setSignatureAlgorithm(\Phar::SHA1);

	$phar->startBuffering();
	$phar->buildFromDirectory(__DIR__ . '/lib/Dwoo');

	$stub = <<<EOF
<?php
/**
 * This file is part of Scion framework.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2013-2014, David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    1.0
 * @date       2014-02-15
 * @package    Dwoo
 */
Phar::mapPhar('{$pharFile}');
require 'phar://{$pharFile}/Autoloader.php';
\$autoloader = new Dwoo\Autoloader();
\$autoloader->add('Dwoo', __FILE__);
\$autoloader->register(true);
__HALT_COMPILER();
EOF;

	$phar->setStub($stub);
	$phar->stopBuffering();
}
catch (UnexpectedValueException $e) {
	echo $e->getMessage();
}
catch (BadMethodCallException $e) {
	echo $e->getMessage();
}