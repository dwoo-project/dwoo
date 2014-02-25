<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Exception\CompilationException;
use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Checks whether an extended file has been modified, and if so recompiles the current template. This is for internal use only, do not use.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-25
 * @package    Dwoo
 */
class FunctionExtendsCheck extends Plugin implements ICompilable {

	public static function compile(Compiler $compiler, $file) {
		preg_match('#^["\']([a-z]{2,}):(.*?)["\']$#i', $file, $m);
		$resource   = $m[1];
		$identifier = str_replace('\\\\', '\\', $m[2]);

		$tpl = $compiler->getCore()->templateFactory($resource, $identifier);

		if ($tpl === null) {
			throw new CompilationException($compiler, 'Load Templates : Resource "' . $resource . ':' . $identifier . '" not found.');
		}
		elseif ($tpl === false) {
			throw new CompilationException($compiler, 'Load Templates : Resource "' . $resource . '" does not support includes.');
		}


		$out = '// checking for modification in ' . $resource . ':' . $identifier . "\r\n";

		$modCheck = $tpl->getIsModifiedCode();

		if ($modCheck) {
			$out .= 'if (!(' . $modCheck . ')) { ob_end_clean(); return false; }';
		}
		else {
			$out .= 'try {
	$tpl = $this->templateFactory("' . $resource . '", "' . $identifier . '");
} catch (\Dwoo\Exception $e) {
	$this->triggerError(\'Load Templates : Resource <em>' . $resource . '</em> was not added to Dwoo, can not extend <em>' . $identifier . '</em>\', E_USER_WARNING);
}
if ($tpl === null)
	$this->triggerError(\'Load Templates : Resource "' . $resource . ':' . $identifier . '" was not found.\', E_USER_WARNING);
elseif ($tpl === false)
	$this->triggerError(\'Load Templates : Resource "' . $resource . '" does not support extends.\', E_USER_WARNING);
if ($tpl->getUid() != "' . $tpl->getUid() . '") { ob_end_clean(); return false; }';
		}

		return $out;
	}
}
