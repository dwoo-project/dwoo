<?php

class PluginLoaderBlockTest extends Dwoo\Block\Plugin implements Dwoo\ICompilable\Block
{
    public function init()
    {
    }

    public static function preProcessing(Dwoo\Compiler $compiler, array $params, $prepend, $append, $type)
    {
        return Dwoo\Compiler::PHP_OPEN . $prepend . ' ob_start(); ' . $append . Dwoo\Compiler::PHP_CLOSE;
    }

    public static function postProcessing(Dwoo\Compiler $compiler, array $params, $prepend, $append, $content)
    {
        return $content . Dwoo\Compiler::PHP_OPEN . $prepend . ' $tmp = ob_get_clean(); echo ucfirst($tmp); ' . $append . Dwoo\Compiler::PHP_CLOSE;
    }
}
