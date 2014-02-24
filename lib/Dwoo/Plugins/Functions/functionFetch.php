<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Reads a file
 * <pre>
 *  * file : path or URI of the file to read (however reading from another website is not recommended for performance reasons)
 *  * assign : if set, the file will be saved in this variable instead of being output
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
class FunctionFetch extends Plugin {

	public function process($file, $assign = null) {
		if ($file === '') {
			return null;
		}

		if ($policy = $this->core->getSecurityPolicy()) {
			while (true) {
				if (preg_match('{^([a-z]+?)://}i', $file)) {
					$this->core->triggerError('The security policy prevents you to read files from external sources.', E_USER_WARNING);
					return null;
				}

				$file = realpath($file);
				$dirs = $policy->getAllowedDirectories();
				foreach ($dirs as $dir => $dummy) {
					if (strpos($file, $dir) === 0) {
						break 2;
					}
				}

				$this->core->triggerError('The security policy prevents you to read <em>' . $file . '</em>', E_USER_WARNING);
				return null;
			}
		}
		$file = str_replace(array("\t", "\n", "\r"), array('\\t', '\\n', '\\r'), $file);

		$out = file_get_contents($file);

		if ($assign === null) {
			return $out;
		}
		$this->core->assignInScope($out, $assign);

		return null;
	}
}
