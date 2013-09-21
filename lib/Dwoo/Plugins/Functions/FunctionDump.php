<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Core;
use Ref\Ref;

function functionDump(Core $core, $value) {
	// options (operators) gathered by the expression parser;
	// this variable gets passed as reference to getInputExpressions(), which will store the operators in it
	$options = array();

	$ref = new Ref();
	Ref::config('stylePath', Ref::ROOT . '/Resources/ref.css');
	Ref::config('scriptPath', Ref::ROOT . '/Resources/ref.js');

	// names of the arguments that were passed to this function
	$expressions = Ref::getInputExpressions($options);
	$capture     = in_array('@', $options, true);

	// something went wrong while trying to parse the source expressions?
	// if so, silently ignore this part and leave out the expression info
	if (func_num_args() !== count($expressions)) {
		$expressions = null;
	}

	// IE goes funky if there's no doctype
	if (!$capture && !headers_sent() && !ob_get_level()) {
		print '<!DOCTYPE HTML><html><head><title>REF</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>';
	}

	if ($capture) {
		ob_start();
	}

	/*foreach ($args as $index => $arg) {
		$ref->query($arg, $expressions ? $expressions[$index] : null);
	}*/
	$ref->query($value, 'Dwoo dump');

	// return the results if this function was called with the error suppression operator
	if ($capture) {
		return ob_get_clean();
	}

	// stop the script if this function was called with the bitwise not operator
	if (in_array('~', $options, true)) {
		print '</body></html>';
		exit(0);
	}
}