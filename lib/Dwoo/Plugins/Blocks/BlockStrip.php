<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Strips the spaces at the beginning and end of each line and also the line breaks
 * <pre>
 *  * mode : sets the content being stripped, available mode are 'default' or 'js'
 *    for javascript, which strips the comments to prevent syntax errors
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-03
 * @package    Dwoo
 */
class BlockStrip extends Plugin implements Block {

	public function begin($mode = "default") {

	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$params = $compiler->getCompiledParams($params);

		$mode = trim($params['mode'], '"\'');
		switch ($mode) {
			case 'js':
			case 'javascript':
				$content = preg_replace('#(?<!:)//\s[^\r\n]*|/\*.*?\*/#s', '', $content);

			case 'default':
			default:
		}

		$content = preg_replace(array("/\n/", "/\r/", '/(<\?(?:php)?|<%)\s*/'), array('', '', '$1 '), preg_replace('#^\s*(.+?)\s*$#m', '$1', $content));

		return $content;
	}
}