<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Plugins\Blocks
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Blocks;

use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;
use Dwoo\Compilation\Exception as CompilationException;

/**
 * Marks the contents of the block as dynamic. Which means that it will not be cached.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginDynamic extends BlockPlugin implements ICompilableBlock
{
    /**
     *
     */
    public function init()
    {
    }

    /**
     * @param Compiler $compiler
     * @param array    $params
     * @param string   $prepend
     * @param string   $append
     * @param string   $type
     *
     * @return string
     */
    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type)
    {
        return '';
    }

    /**
     * @param Compiler $compiler
     * @param array    $params
     * @param string   $prepend
     * @param string   $append
     * @param string   $content
     *
     * @return string
     */
    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
    {
        try {
            $compiler->findBlock('dynamic');

            return $content;
        }
        catch (CompilationException $e) {
        }
        $output = Compiler::PHP_OPEN . 'if($doCache) {' . "\n\t" . 'echo \'<dwoo:dynamic_\'.$dynamicId.\'>' . str_replace('\'', '\\\'', $content) . '</dwoo:dynamic_\'.$dynamicId.\'>\';' . "\n} else {\n\t";
        if (substr($content, 0, strlen(Compiler::PHP_OPEN)) == Compiler::PHP_OPEN) {
            $output .= substr($content, strlen(Compiler::PHP_OPEN));
        } else {
            $output .= Compiler::PHP_CLOSE . $content;
        }
        if (substr($output, - strlen(Compiler::PHP_CLOSE)) == Compiler::PHP_CLOSE) {
            $output = substr($output, 0, - strlen(Compiler::PHP_CLOSE));
        } else {
            $output .= Compiler::PHP_OPEN;
        }
        $output .= "\n}" . Compiler::PHP_CLOSE;

        return $output;
    }

    /**
     * @param $output
     * @param $dynamicId
     * @param $compiledFile
     *
     * @return mixed|string
     */
    public static function unescape($output, $dynamicId, $compiledFile)
    {
        $output = preg_replace_callback('/<dwoo:dynamic_(' . $dynamicId . ')>(.+?)<\/dwoo:dynamic_' . $dynamicId . '>/s', array(
            'self',
            'unescapePhp'
        ), $output, - 1, $count);
        // re-add the includes on top of the file
        if ($count && preg_match('#/\* template head \*/(.+?)/\* end template head \*/#s', file_get_contents($compiledFile), $m)) {
            $output = '<?php ' . $m[1] . ' ?>' . $output;
        }

        return $output;
    }

    /**
     * @param $match
     *
     * @return mixed
     */
    public static function unescapePhp($match)
    {
        return preg_replace('{<\?php /\*' . $match[1] . '\*/ echo \'(.+?)\'; \?>}s', '$1', $match[2]);
    }
}
