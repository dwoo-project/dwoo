<?php
namespace Dwoo\Smarty\Filter;

use Dwoo\Filter;

class Adapter extends Filter {
	public $callback;

	public function process($input) {
		return call_user_func($this->callback, $input);
	}

	public function registerCallback($callback) {
		$this->callback = $callback;
	}
}