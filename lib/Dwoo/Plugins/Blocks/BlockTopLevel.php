<?php
namespace Dwoo\Plugins\Blocks;

use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Internal plugin used to wrap the template output, do not use in your templates as it will break them
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-01
 * @package    Dwoo
 */
final class BlockTopLevel extends Plugin implements Block {
	public function begin() {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return '/* end template head */ ob_start(); /* template body */ ' . Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		return $content . Compiler::PHP_OPEN . ' /* end template body */' . "\n" . 'return $this->buffer . ob_get_clean();';
	}
}