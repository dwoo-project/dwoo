<?php

namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Formats a float using the number_format function, see {@link http://php.net/number-format} for details
 */
class PluginNumberFormat extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param float    $value
     * @param int      $decimals
     * @param string   $dec_point
     * @param string   $thousands_sep
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, $decimals = 0, $dec_point = ".",  $thousands_sep = ",")
    {
        return 'number_format(' . $value . ',' . $decimals . ',' . $dec_point . ',' . $thousands_sep . ')';
    }
}
