<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;
use Dwoo\Template\String;

/**
 * Evaluates the given string as if it was a template
 *
 * Although this plugin is kind of optimized and will
 * not recompile your string each time, it is still not
 * a good practice to use it. If you want to have templates
 * stored in a database or something you should probably use
 * the String class or make another class that
 * extends it
 * <pre>
 *  * var : the string to use as a template
 *  * assign : if set, the output of the template will be saved in this variable instead of being output
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionEval extends Plugin {

	public function process($var, $assign = null) {
		if ($var == '') {
			return null;
		}

		$tpl   = new String($var);
		$clone = clone $this->core;
		$out   = $clone->get($tpl, $this->core->readVar('_parent'));

		if ($assign !== null) {
			$this->core->assignInScope($out, $assign);
		}
		else {
			return $out;
		}

		return null;
	}
}