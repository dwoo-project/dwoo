<?php

require_once dirname(dirname(__FILE__)).'/Dwoo.php';

class DwooTests {
	public static function suite() {
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_PATH.'plugins/builtin');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'Dwoo.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooCompiler.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooData.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooInterfaces.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooPlugin.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooSmartyAdapter.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooTemplateFile.php');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_PATH.'DwooTemplateString.php');

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