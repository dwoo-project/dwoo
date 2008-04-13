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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
function DwooPlugin_extendsCheck_compile(DwooCompiler $compiler, $file, $uid)
{
	preg_match('#^["\']([a-z]{2,}):(.*?)["\']$#i', $file, $m);
	$resource = $m[1];
	$identifier = $m[2];

	return '\'\'; // check for modification in '.$resource.':'.$identifier.'
try {
	$tpl = $this->getTemplate("'.$resource.'", "'.$identifier.'");
} catch (DwooException $e) {
	$this->triggerError(\'Extends : Resource <em>'.$resource.'</em> was not added to Dwoo, can not include <em>'.$identifier.'</em>\', E_USER_WARNING);
}
if($tpl === null)
	$this->triggerError(\'Extends : Resource "'.$resource.':'.$identifier.'" was not found.\', E_USER_WARNING);
elseif($tpl === false)
	$this->triggerError(\'Include : Extending "'.$resource.':'.$identifier.'" was not allowed for an unknown reason.\', E_USER_WARNING);
if($tpl->getUid() !== "'.substr($uid, 1, -1).'") { ob_end_clean(); return false; }';
}

?>