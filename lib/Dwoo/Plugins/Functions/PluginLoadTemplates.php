<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo\Plugins\Functions
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE LGPLv3
 * @version   1.3.6
 * @date      2017-03-21
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\Compilation\Exception as CompilationException;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Loads sub-templates contained in an external file
 * <pre>
 *  * file : the resource name of the file to load
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginLoadTemplates extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param string   $file
     *
     * @return string
     * @throws CompilationException
     */
    public static function compile(Compiler $compiler, $file)
    {
        $file = substr($file, 1, - 1);

        if ($file === '') {
            return '';
        }

        if (preg_match('#^([a-z]{2,}):(.*)$#i', $file, $m)) {
            // resource:identifier given, extract them
            $resource   = $m[1];
            $identifier = $m[2];
        } else {
            // get the current template's resource
            $resource   = $compiler->getCore()->getTemplate()->getResourceName();
            $identifier = $file;
        }

        $tpl = $compiler->getCore()->templateFactory($resource, $identifier);

        if ($tpl === null) {
            throw new CompilationException($compiler,
                'Load Templates : Resource "' . $resource . ':' . $identifier . '" not found.');
        } elseif ($tpl === false) {
            throw new CompilationException($compiler,
                'Load Templates : Resource "' . $resource . '" does not support includes.');
        }

        $cmp = clone $compiler;
        $cmp->compile($compiler->getCore(), $tpl);
        foreach ($cmp->getTemplatePlugins() as $template => $args) {
            $compiler->addTemplatePlugin($template, $args['params'], $args['uuid'], $args['body']);
        }
        foreach ($cmp->getUsedPlugins() as $plugin => $type) {
            $compiler->addUsedPlugin($plugin, $type);
        }

        $out = '\'\';// checking for modification in ' . $resource . ':' . $identifier . "\r\n";

        $modCheck = $tpl->getIsModifiedCode();

        if ($modCheck) {
            $out .= 'if (!(' . $modCheck . ')) { ob_end_clean(); return false; }';
        } else {
            $out .= 'try {
	$tpl = $this->templateFactory("' . $resource . '", "' . $identifier . '");
} catch (Dwoo\Exception $e) {
	$this->triggerError(\'Load Templates : Resource <em>' . $resource . '</em> was not added to Dwoo, can not extend <em>' . $identifier . '</em>\', E_USER_WARNING);
}
if ($tpl === null)
	$this->triggerError(\'Load Templates : Resource "' . $resource . ':' . $identifier . '" was not found.\', E_USER_WARNING);
elseif ($tpl === false)
	$this->triggerError(\'Load Templates : Resource "' . $resource . '" does not support extends.\', E_USER_WARNING);
if ($tpl->getUid() != "' . $tpl->getUid() . '") { ob_end_clean(); return false; }';
        }

        return $out;
    }
}