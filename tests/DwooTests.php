<?php

define('DWOO_CACHEDIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'cache');
define('DWOO_COMPILEDIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'compiled');
require dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Dwoo.php';

class DwooTests {
	public static function suite() {
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_DIR.'plugins/builtin');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'Dwoo.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooCompiler.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooData.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooInterfaces.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooPlugin.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooSmartyAdapter.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooTemplateFile.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIR.'DwooTemplateString.php');

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