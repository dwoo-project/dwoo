<?php

/**
 * Internal plugin used to wrap the template output, do not use in your templates as it will break them
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
final class Dwoo_Plugin_topLevelBlock extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public function init()
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
	{
		return 'ob_start(); '.Dwoo_Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
	{
		return $content . Dwoo_Compiler::PHP_OPEN.'return $this->buffer . ob_get_clean();';
	}
}
