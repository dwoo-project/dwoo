<?php

error_reporting(E_ALL|E_STRICT);
if(!ini_get('date.timezone'))
	date_default_timezone_set('CET');
define('DWOO_CACHE_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'cache');
define('DWOO_COMPILE_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'compiled');
require dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Dwoo.php';

class DwooTests {
	public static function suite() {
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_DIRECTORY.'plugins/builtin');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'Dwoo.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooCompiler.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooData.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooInterfaces.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooPlugin.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooSmartyAdapter.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooTemplateFile.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'DwooTemplateString.php');

		$suite = new PHPUnit_Framework_TestSuite('Dwoo - Unit Tests Report');

		foreach(new DirectoryIterator(dirname(__FILE__)) as $file) {
			if(!$file->isDot() && !$file->isDir() && (string) $file !== 'DwooTests.php' && substr((string) $file, -4) === '.php')
			{
				require_once $file->getPathname();
				$suite->addTestSuite(basename($file, '.php'));
			}
		}

		return $suite;
	}
}

?>