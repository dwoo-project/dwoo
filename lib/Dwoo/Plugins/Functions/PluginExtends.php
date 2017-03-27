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
use Dwoo\ICompilable;
use Dwoo\Plugin;
use Dwoo\Exception as Exception;
use Dwoo\Security\Exception as SecurityException;
use Dwoo\Compilation\Exception as CompilationException;

/**
 * Extends another template, read more about template inheritance at {@link
 * http://wiki.dwoo.org/index.php/TemplateInheritance}
 * <pre>
 *  * file : the template to extend
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginExtends extends Plugin implements ICompilable
{
    protected static $childSource;
    protected static $regex;
    protected static $l;
    protected static $r;
    protected static $lastReplacement;

    public function process()
    {
    }

    /**
     * @param Compiler $compiler
     * @param          $file
     *
     * @throws CompilationException
     */
    public static function compile(Compiler $compiler, $file)
    {
        list($l, $r) = $compiler->getDelimiters();
        self::$l     = preg_quote($l, '/');
        self::$r     = preg_quote($r, '/');
        self::$regex = '/
			' . self::$l . 'block\s(["\']?)(.+?)\1' . self::$r . '(?:\r?\n?)
			((?:
				(?R)
				|
				[^' . self::$l . ']*
				(?:
					(?! ' . self::$l . '\/?block\b )
					' . self::$l . '
					[^' . self::$l . ']*+
				)*
			)*)
			' . self::$l . '\/block' . self::$r . '
			/six';

        if ($compiler->getLooseOpeningHandling()) {
            self::$l .= '\s*';
            self::$r = '\s*' . self::$r;
        }
        $inheritanceTree = array(array('source' => $compiler->getTemplateSource()));
        $curPath         = dirname($compiler->getCore()->getTemplate()->getResourceIdentifier()) . DIRECTORY_SEPARATOR;
        $curTpl          = $compiler->getCore()->getTemplate();

        while (!empty($file)) {
            if ($file === '""' || $file === "''" || (substr($file, 0, 1) !== '"' && substr($file, 0, 1) !== '\'')) {
                throw new CompilationException($compiler, 'Extends : The file name must be a non-empty string');
            }

            if (preg_match('#^["\']([a-z]{2,}):(.*?)["\']$#i', $file, $m)) {
                // resource:identifier given, extract them
                $resource   = $m[1];
                $identifier = $m[2];
            } else {
                // get the current template's resource
                $resource   = $curTpl->getResourceName();
                $identifier = substr($file, 1, - 1);
            }

            try {
                $parent = $compiler->getCore()->templateFactory($resource, $identifier, null, null, null, $curTpl);
            }
            catch (SecurityException $e) {
                throw new CompilationException($compiler, 'Extends : Security restriction : ' . $e->getMessage());
            }
            catch (Exception $e) {
                throw new CompilationException($compiler, 'Extends : ' . $e->getMessage());
            }

            if ($parent === null) {
                throw new CompilationException($compiler, 'Extends : Resource "' . $resource . ':' . $identifier . '" not found.');
            } elseif ($parent === false) {
                throw new CompilationException($compiler, 'Extends : Resource "' . $resource . '" does not support extends.');
            }

            $curTpl    = $parent;
            $newParent = array(
                'source'     => $parent->getSource(),
                'resource'   => $resource,
                'identifier' => $parent->getResourceIdentifier(),
                'uid'        => $parent->getUid()
            );
            if (array_search($newParent, $inheritanceTree, true) !== false) {
                throw new CompilationException($compiler, 'Extends : Recursive template inheritance detected');
            }
            $inheritanceTree[] = $newParent;

            if (preg_match('/^' . self::$l . 'extends(?:\(?\s*|\s+)(?:file=)?\s*((["\']).+?\2|\S+?)\s*\)?\s*?' . self::$r . '/i', $parent->getSource(), $match)) {
                $curPath = dirname($identifier) . DIRECTORY_SEPARATOR;
                if (isset($match[2]) && $match[2] == '"') {
                    $file = '"' . str_replace('"', '\\"', substr($match[1], 1, - 1)) . '"';
                } elseif (isset($match[2]) && $match[2] == "'") {
                    $file = '"' . substr($match[1], 1, - 1) . '"';
                } else {
                    $file = '"' . $match[1] . '"';
                }
            } else {
                $file = false;
            }
        }

        while (true) {
            $parent                = array_pop($inheritanceTree);
            $child                 = end($inheritanceTree);
            self::$childSource     = $child['source'];
            self::$lastReplacement = count($inheritanceTree) === 1;
            if (!isset($newSource)) {
                $newSource = $parent['source'];
            }
            $newSource = preg_replace_callback(self::$regex, array(
                'Dwoo\Plugins\Functions\PluginExtends',
                'replaceBlock'
            ), $newSource);
            $newSource = $l . 'do extendsCheck(' . var_export($parent['resource'] . ':' . $parent['identifier'], true) . ')' . $r . $newSource;

            if (self::$lastReplacement) {
                break;
            }
        }
        $compiler->setTemplateSource($newSource);
        $compiler->recompile();
    }

    /**
     * @param array $matches
     *
     * @return mixed|string
     */
    protected static function replaceBlock(array $matches)
    {
        $matches[3] = self::removeTrailingNewline($matches[3]);

        if (preg_match_all(self::$regex, self::$childSource, $override) && in_array($matches[2], $override[2])) {
            $key      = array_search($matches[2], $override[2]);
            $override = self::removeTrailingNewline($override[3][$key]);

            $l = stripslashes(self::$l);
            $r = stripslashes(self::$r);

            if (self::$lastReplacement) {
                return preg_replace('/' . self::$l . '\$dwoo\.parent' . self::$r . '/is', $matches[3], $override);
            }

            return $l . 'block ' . $matches[1] . $matches[2] . $matches[1] . $r . preg_replace('/' . self::$l . '\$dwoo\.parent' . self::$r . '/is', $matches[3], $override) . $l . '/block' . $r;
        }

        if (preg_match(self::$regex, $matches[3])) {
            return preg_replace_callback(self::$regex, array(
                'Dwoo\Plugins\Functions\PluginExtends',
                'replaceBlock'
            ), $matches[3]);
        }

        if (self::$lastReplacement) {
            return $matches[3];
        }

        return $matches[0];
    }

    /**
     * @param $text
     *
     * @return string
     */
    protected static function removeTrailingNewline($text)
    {
        return substr($text, - 1) === "\n" ? substr($text, - 2, 1) === "\r" ? substr($text, 0, - 2) : substr($text, 0, - 1) : $text;
    }
}
