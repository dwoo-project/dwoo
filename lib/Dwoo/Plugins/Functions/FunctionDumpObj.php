<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Plugin;

/**
 * Dumps an object with all properties (public, protected, private)
 * <pre>
 *  * obj : the object to display
 * </pre>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Stephan Wentz <swentz@brainbits.net>
 * @copyright  Copyright (c) 2008, brainbits GmbH
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-09
 * @package    Dwoo
 */
class FunctionDumpObj extends Plugin {

	public function process($obj) {
		if (! is_object($obj)) {
			return 'Given var is not an object';
		}

		$reflectionObject = new \ReflectionObject($obj);
		//var_dump($reflectionObject->getMethods());
		//var_dump($reflectionObject->getProperties());

		$out = '<div style="background:#aaa; padding:5px; margin:5px; color:#000;">obj_dump:';
		$out .= '<div style="padding-left:20px;">';

		$out .= $this->exportObj($obj);

		return $out . '</div></div>';
	}

	protected function exportObj($obj) {
		$list = (array)$obj;

		$protectedLength = strlen(get_class($obj)) + 2;

		$out = array();
		foreach ($list as $attributeName => $attributeValue) {
			if (property_exists($obj, $attributeName)) {
				$key = 'public';
			}
			elseif (substr($attributeName, 0, 3) === "\0*\0") {
				$key           = 'protected';
				$attributeName = substr($attributeName, 3);
			}
			else {
				$key           = 'private';
				$attributeName = substr($attributeName, $protectedLength);
			}

			$out[$key] .= '<div>(' . $key . ') ' . $attributeName . ': ';

			if (is_array($attributeValue)) {
				$out[$key] .= '</div><div style="padding-left:20px;">' . $this->export($attributeValue);
			}
			else {
				// TODO 1st value need to be tested
				$out[$key] .= $this->exportVar($attributeName, $attributeValue);
			}

			$out[$key] .= '</div>';
		}

		$return = '';

		if (! empty($out['public'])) {
			$return .= $out['public'];
		}

		if (! empty($out['protected'])) {
			$return .= $out['protected'];
		}

		if (! empty($out['private'])) {
			$return .= $out['private'];
		}

		return $return;
	}

	protected function export($var) {
		$out = '';
		foreach ($var as $i => $v) {
			if (is_array($v) || (is_object($v) && $v instanceof \Iterator)) {
				$out .= $i . ' (' . (is_array($v) ? 'array' : 'object:' . get_class($v)) . ')';
				$out .= ':<div style="padding-left:30px;">' . $this->export($v) . '</div>';
			}
			else {
				$out .= $this->exportVar($i . ' = ', $v);
			}
		}

		return $out;
	}

	protected function exportVar($i, $v) {
		if (is_string($v) || is_bool($v) || is_numeric($v)) {
			return $i . htmlentities(var_export($v, true)) . '<br />';
		}
		elseif (is_null($v)) {
			return $i . 'null<br />';
		}
		elseif (is_object($v)) {
			return $i . 'object(' . get_class($v) . ')<br />';
		}
		elseif (is_resource($v)) {
			return $i . 'resource(' . get_resource_type($v) . ')<br />';
		}
		else {
			return $i . htmlentities(var_export($v, true)) . '<br />';
		}
	}
}