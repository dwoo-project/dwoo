<?php
use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * Internal plugin used to wrap the template output, do not use in your templates as it will break them.
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 *
 * @link       http://dwoo.org/
 *
 * @version    1.2.3
 * @date       2016-10-15
 */
final class Dwoo_Plugin_topLevelBlock extends BlockPlugin implements ICompilableBlock
{
    public function init()
    {
    }

    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type)
    {
        return '/* end template head */ ob_start(); /* template body */ '.Compiler::PHP_CLOSE;
    }

    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
    {
        return $content.Compiler::PHP_OPEN.' /* end template body */'."\n".'return $this->buffer . ob_get_clean();';
    }
}
