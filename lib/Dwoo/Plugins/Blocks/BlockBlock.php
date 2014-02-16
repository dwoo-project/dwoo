<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * This is used only when rendering a template that has blocks but is not extending anything,
 * it doesn't do anything by itself and should not be used outside of template inheritance context,
 * see {@link http://wiki.dwoo.org/index.php/TemplateInheritance} to read more about it.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    1.0.0
 * @date       2008-10-23
 * @package    Dwoo
 */
class BlockBlock extends Plugin implements Block {
	public function begin($name = '') {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		return $content;
	}
}