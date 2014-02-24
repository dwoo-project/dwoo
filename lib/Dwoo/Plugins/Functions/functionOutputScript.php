<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;
use Dwoo\Plugins\Blocks\BlockScriptCapture;

/**
 * Ouputs the content captured by the script_capture block
 * <pre>
 *  * name : capture name
 * </pre>
 *
 * Used with the output_script function
 * Example :
 *
 * <code>
 * {script_capture "myscript"}
 *   alert('hello world');
 * {/script_capture}
 * {output_script myscript}
 * </code>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Harro van der Klauw <mroximoron@gmail.com>
 * @copyright  Copyright (c) 2008, Harro van der Klauw
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionOutputScript extends Plugin {

	public function process($name) {
		$pre  = '<script type="text/javascript" charset="utf-8">';
		$post = '</script>';

		if (!class_exists('\Dwoo\Plugins\Blocks\BlockScriptCapture')) {
			$this->core->getLoader()->loadPlugin('scriptCapture');
		}

		$content = trim(BlockScriptCapture::getScripts($name));
		if (empty($content)) {
			return '';
		}

		return $pre . $content . $post;
	}
}
