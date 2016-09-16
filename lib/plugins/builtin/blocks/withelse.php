<?php
use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * This plugin serves as a {else} block specifically for the {with} plugin.
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
class Dwoo_Plugin_withelse extends BlockPlugin implements ICompilableBlock
{
    public function init()
    {
    }

    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type)
    {
        $with = &$compiler->findBlock('with', true);

        $params['initialized'] = true;
        $compiler->injectBlock($type, $params);

        return '';
    }

    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
    {
        if (!isset($params['initialized'])) {
            return '';
        }

        $block = &$compiler->getCurrentBlock();
        $block['params']['hasElse'] = Compiler::PHP_OPEN."else {\n".Compiler::PHP_CLOSE.$content.Compiler::PHP_OPEN."\n}".Compiler::PHP_CLOSE;

        return '';
    }
}
