<?php

error_reporting(E_ALL|E_STRICT);
if (!ini_get('date.timezone'))
	date_default_timezone_set('CET');
define('DWOO_CACHE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'cache');
define('DWOO_COMPILE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'compiled');

require dirname(__FILE__) . '/'.DwooTests::getConfig('DWOO_PATH').'/dwooAutoload.php';
//require dirname(__FILE__) . '/'.DwooTests::getConfig('DWOO_PATH').'/Dwoo.compiled.php';
//set_include_path(get_include_path().';'.DWOO_DIRECTORY);
define('TEST_DIRECTORY', dirname(__FILE__));

class DwooTests extends PHPUnit_Framework_TestSuite {

	protected static $cfg;

	public static function getConfig($var, $default=null) {
		if (self::$cfg == null) {
			self::$cfg = parse_ini_file(dirname(__FILE__) . '/config.ini');
		}
		if (isset(self::$cfg[$var])) {
			return self::$cfg[$var];
		}
		return $default;
	}

	public static function suite() {
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_DIRECTORY.'plugins/builtin');
		PHPUnit_Util_Filter::addDirectoryToWhitelist(DWOO_DIRECTORY.'Dwoo');
		PHPUnit_Util_Filter::addFileToWhitelist(DWOO_DIRECTORY.'Dwoo.php');
		PHPUnit_Util_Filter::removeDirectoryFromWhitelist(DWOO_DIRECTORY.'Dwoo/Adapters');

		$suite = new self('Dwoo - Unit Tests Report');

		foreach (new DirectoryIterator(dirname(__FILE__)) as $file) {
			if (!$file->isDot() && !$file->isDir() && (string) $file !== 'DwooTests.php' && substr((string) $file, -4) === '.php') {
				require_once $file->getPathname();
				$class = basename($file, '.php');
				// to have an optional test suite, it should implement a public static function isRunnable
				// that returns true only if all the conditions are met to run it successfully, for example
				// it can check that an external library is present
				if (!method_exists($file, 'isRunnable') || call_user_func(array($file, 'isRunnable'))) {
					$suite->addTestSuite($class);
				}
			}
		}

		return $suite;
	}

	protected function tearDown() {
		$this->clearDir(TEST_DIRECTORY.'/temp/cache', true);
		$this->clearDir(TEST_DIRECTORY.'/temp/compiled', true);
	}

	protected function clearDir($path, $emptyOnly=false)
	{
		if (is_dir($path)) {
			foreach (glob($path.'/*') as $f) {
				$this->clearDir($f);
			}
			if (!$emptyOnly) {
				rmdir($path);
			}
		} else {
			unlink($path);
		}
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
		return 'equals expected value.'.PHP_EOL.'Expected:'.PHP_EOL.$this->target.PHP_EOL.'Received:'.PHP_EOL.$this->other.PHP_EOL;
	}
}

class DwooConstraintPathEquals extends PHPUnit_Framework_Constraint
{
	protected $target;

	public function __construct($target)
	{
		$this->target = preg_replace('#([\\\\/]{1,2})#', '/', $target);
	}

	public function evaluate($other)
	{
		$this->other = preg_replace('#([\\\\/]{1,2})#', '/', $other);
		return $this->target == $this->other;
	}

	public function toString()
	{
		return 'equals expected value.'.PHP_EOL.'Expected:'.PHP_EOL.$this->target.PHP_EOL.'Received:'.PHP_EOL.$this->other.PHP_EOL;
	}
}