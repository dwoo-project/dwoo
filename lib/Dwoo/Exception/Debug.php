<?php
namespace Dwoo\Exception;

use Dwoo\Core;

class Debug {

	private static $_debugger = 'DEBUGGER INIT<br>';
	private static $_result = '';

	public static function getDebugger() {
		$exception = file_get_contents('lib/resources/exception.html');
		$exception = str_replace('{$DETAILS}', self::$_debugger, $exception);
		$exception = str_replace('{$RESULT}', self::$_result, $exception);
		$exception = str_replace('{$PHP_VERSION}', phpversion(), $exception);
		$exception = str_replace('{$DWOO_VERSION}', Core::VERSION, $exception);
		$exception = str_replace('{$EXECTIME}', round(microtime(true)-$_SERVER['REQUEST_TIME'], 3), $exception);
		$exception = str_replace('{$MEMORY_USAGE}', self::_convertMemoryUsage(memory_get_usage(true)), $exception);

		echo $exception;
	}

	public static function setMessage($msg) {
		self::$_debugger .= $msg;
	}

	public static function setResult($content) {
		self::$_result = $content;
	}

	private static function _convertMemoryUsage($size) {
		$unit = array('B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
	}
}