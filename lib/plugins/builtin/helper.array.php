<?php

/**
 * Builds an array with all the provided variables, use named parameters to make an associative array
 * <pre>
 *  * rest : any number of variables, strings or anything that you want to store in the array
 * </pre>
 * Example :
 *
 * <code>
 * {array(a, b, c)} results in array(0=>'a', 1=>'b', 2=>'c')
 * {array(a=foo, b=5, c=array(4,5))} results in array('a'=>'foo', 'b'=>5, 'c'=>array(0=>4, 1=>5))
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
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
function Dwoo_Plugin_array_compile(Dwoo_Compiler $compiler, array $rest=array())
{
	$out = array();
	foreach($rest as $k=>$v)
		if(is_numeric($k))
			$out[] = $k.'=>'.$v;
		else
			$out[] = '"'.$k.'"=>'.$v;

	return 'array('.implode(', ', $out).')';
}
