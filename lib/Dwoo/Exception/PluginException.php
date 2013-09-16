<?php
namespace Dwoo\Exception;

class PluginException extends Exception {

	const FORGOT_BIND = 'Plugin <em>%s</em> can not be found, maybe you forgot to bind it if it\'s a custom plugin ?';
	const NOT_FOUND = 'Plugin "%s" could not be found';

	public function __construct($message = "", $code = 0, Exception $previous = null) {
		// Register parent class as an error handler.
		parent::__construct($message, $code, $previous);
	}
}