<?php
namespace Dwoo\Smarty\Processor;

use Dwoo\Processor;

class Adapter extends Processor {
	public $callback;

	public function process($input) {
		return call_user_func($this->callback, $input);
	}

	public function registerCallback($callback) {
		$this->callback = $callback;
	}
}