<?php

/**
 * Strips the spaces at the beginning and end of each line and also the line breaks
 * <pre>
 *  * mode : sets the content being stripped, available mode are 'default' or 'js'
 *    for javascript, which strips the comments to prevent syntax errors
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
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
class Dwoo_Plugin_strip extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public function init($mode = "default")
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
	{
		return '';
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
	{
		$params = $compiler->getCompiledParams($params);

		$mode = trim($params['mode'], '"\'');
		switch ($mode) {
			case 'js':
			case 'javascript':
				$content = preg_replace('#(?<!:)//\s[^\r\n]*|/\*.*?\*/#','', $content);

			case 'default':
			default:
		}

		$content = preg_replace(array("/\n/","/\r/",'/(<\?(?:php)?|<%)\s*/'), array('','','$1 '), preg_replace('#^\s*(.+?)\s*$#m', '$1', $content));

		return $content;
	}
}
