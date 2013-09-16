<?php
namespace Dwoo\Exception;

use Dwoo\Core;

class Exception extends \Dwoo\Exception {

	public function __construct($message = "", $code = 0, \Dwoo\Exception $previous = null) {
		$this->register();

		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}

	public function handleException(int $errno , string $errstr, string $errfile, int $errline, array $errcontext) {
		var_dump($errstr);
	}

	public function handleError(\Dwoo\Exception $e) {

		$html = new \DOMDocument();
		$html->loadHTMLFile('lib/resources/exception.html');

		$message = $html->getElementById('message');
		$template = $html->createDocumentFragment();
		$template->appendXML($e->getMessage());
		$message->appendChild($template);
		unset($template);

		$php_version = $html->getElementById('php-version');
		$template = $html->createDocumentFragment();
		$template->appendXML(phpversion());
		$php_version->appendChild($template);
		unset($template);

		$dwoo_version = $html->getElementById('dwoo-version');
		$template = $html->createDocumentFragment();
		$template->appendXML(Core::VERSION);
		$dwoo_version->appendChild($template);
		unset($template);

		$exectime = $html->getElementById('exectime');
		$template = $html->createDocumentFragment();
		$template->appendXML(round(microtime(true)-$_SERVER['REQUEST_TIME'], 3));
		$exectime->appendChild($template);
		unset($template);

		echo $html->saveHTML();
	}

	public function handleShutdown() {
		var_dump(error_get_last());
	}

	/**
	 * Custom string representation of object.
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

	/**
	 * Registers this instance as an error handler.
	 */
	protected function register() {
		set_error_handler(array($this, 'handleException'));
		set_exception_handler(array($this, 'handleError'));
		register_shutdown_function(array($this, 'handleShutdown'));
	}
}