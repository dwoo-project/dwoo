<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Outputs a html &lt;a&gt; tag
 * <pre>
 *  * href : the target URI where the link must point
 *  * rest : any other attributes you want to add to the tag can be added as named parameters
 * </pre>
 * Example :
 * <code>
 * {* Create a simple link out of an url variable and add a special class attribute: *}
 * {a $url class="external" /}
 * {* Mark a link as active depending on some other variable : *}
 * {a $link.url class=tif($link.active "active"); $link.title /}
 * {* This is similar to: <a href="{$link.url}" class="{if $link.active}active{/if}">{$link.title}</a> *}
 * </code>
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
class BlockA extends Plugin implements Block {

	public function begin($href, array $rest = array()) {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		$p = $compiler->getCompiledParams($params);

		$out = Compiler::PHP_OPEN . 'echo \'<a ' . self::paramsToAttributes($p, "'", $compiler);

		return $out . '>\';' . Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$p = $compiler->getCompiledParams($params);

		// no content was provided so use the url as display text
		if ($content == "") {
			// merge </a> into the href if href is a string
			if (substr($p['href'], - 1) === '"' || substr($p['href'], - 1) === '\'') {
				return Compiler::PHP_OPEN . 'echo ' . substr($p['href'], 0, - 1) . '</a>' . substr($p['href'], - 1) . ';' . Compiler::PHP_CLOSE;
			}

			// otherwise append
			return Compiler::PHP_OPEN . 'echo ' . $p['href'] . '.\'</a>\';' . Compiler::PHP_CLOSE;
		}

		// return content
		return $content . '</a>';
	}
}