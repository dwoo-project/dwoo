<?php

/**
 * TOCOM
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooPlugin_dump extends DwooPlugin
{
	public function process($var = '$')
	{
		if ($var === '$') {
			$var = $this->dwoo->getData();
			$out = '<div style="background:#aaa; padding:5px; margin:5px;">data';
		} else {
			$out = '<div style="background:#aaa; padding:5px; margin:5px;">dump';
		}

		if(!is_array($var)) {
			return $this->exportVar('', $var);
		}

		$scope = $this->dwoo->getScope();

		if ($var === $scope) {
			$out .= ' (current scope): <div style="background:#ccc;">';
		} else {
			$out .= ':<div style="padding-left:20px;">';
		}

		$out .= $this->export($var, $scope);

		return $out .'</div></div>';
	}

	protected function export($var, $scope)
	{
		$out = '';
		foreach ($var as $i=>$v) {
			if (is_array($v)) {
				$out .= $i;
				if($v===$scope) {
					$out .= ' (current scope):<div style="background:#ccc;padding-left:20px;">'.$this->export($v, $scope).'</div>';
				} else {
					$out .= ':<div style="padding-left:20px;">'.$this->export($v, $scope).'</div>';
				}
			} else {
				$out .= $this->exportVar($i.' = ', $v);
			}
		}
		return $out;
	}

	protected function exportVar($i, $v)
	{
		if (is_string($v) || is_bool($v) || is_numeric($v)) {
			return $i.var_export($v, true).'<br />';
		} elseif (is_null($v)) {
			return $i.'null<br />';
		} elseif (is_object($v)) {
			return $i.'object('.get_class($v).')<br />';
		} elseif (is_resource($v)) {
			return $i.'resource('.get_resource_type($v).')<br />';
		} else {
			return $i.var_export($v, true).'<br />';
		}
	}
}

?>