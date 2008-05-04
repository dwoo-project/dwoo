<?php

error_reporting(E_ALL|E_STRICT);
if(!ini_get('date.timezone'))
	date_default_timezone_set('CET');
define('DWOO_CACHE_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'cache');
define('DWOO_COMPILE_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'compiled');
require dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Dwoo.php';
define('TEST_DIRECTORY', dirname(__FILE__));

class DwooTests {
	public static function suite() {
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_DIRECTORY.'plugins/builtin');
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_DIRECTORY.'Dwoo');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'Dwoo.php');

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

// Evaluates two strings and ignores differences in line endings (\r\n == \n == \r)
class DwooConstraintStringEquals extends PHPUnit_Framework_Constraint
{
	protected $target;

	public function __construct($target)
	{
		$this->target = preg_replace('#(\r\n|\r)#', "\n", $target);
	}

	public function evaluate($other)
	{
		$this->other = preg_replace('#(\r\n|\r)#', "\n", $other);
		return $this->target == $this->other;
	}

	public function toString()
	{
		return 'equals "'.$this->target.'" / "'.$this->other.'"';
	}
}

?>