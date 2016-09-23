<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Smarty
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-23
 * @link      http://dwoo.org/
 */

namespace Dwoo\Smarty;

use Dwoo\Core;
use Dwoo\Compiler;
use Dwoo\Data;
use Dwoo\Security\Policy as SecurityPolicy;
use Dwoo\Exception as Exception;
use Dwoo\Template\File as TemplateFile;
use Dwoo\Smarty\Filter\Adapter as FilterAdapter;
use Dwoo\Smarty\Processor\Adapter as ProcessorAdapter;

if (!defined('DIR_SEP')) {
    define('DIR_SEP', DIRECTORY_SEPARATOR);
}

if (!defined('SMARTY_PHP_PASSTHRU')) {
    define('SMARTY_PHP_PASSTHRU', 0);
    define('SMARTY_PHP_QUOTE', 1);
    define('SMARTY_PHP_REMOVE', 2);
    define('SMARTY_PHP_ALLOW', 3);
}

/**
 * A Smarty compatibility layer for Dwoo.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class Adapter extends Core
{
    /**
     * Magic get/set/call functions that handle unsupported features
     *
     * @param string $p
     * @param string $v
     */
    public function __set($p, $v)
    {
        if ($p === 'scope') {
            $this->scope = $v;

            return;
        }
        if ($p === 'data') {
            $this->data = $v;

            return;
        }
        if (array_key_exists($p, $this->compat['properties']) !== false) {
            if ($this->show_compat_errors) {
                $this->triggerError('Property ' . $p . ' is not available in the Dwoo\Smarty\Adapter, however it might be implemented in the future, check out http://wiki.dwoo.org/index.php/SmartySupport for more details.', E_USER_NOTICE);
            }
            $this->compat['properties'][$p] = $v;
        } else {
            if ($this->show_compat_errors) {
                $this->triggerError('Property ' . $p . ' is not available in the Dwoo\Smarty\Adapter, but it is not listed as such, so you might want to tell me about it at j.boggiano@seld.be', E_USER_NOTICE);
            }
        }
    }

    /**
     * @param $p
     *
     * @return mixed
     */
    public function __get($p)
    {
        if (array_key_exists($p, $this->compat['properties']) !== false) {
            if ($this->show_compat_errors) {
                $this->triggerError('Property ' . $p . ' is not available in the Dwoo\Smarty\Adapter, however it might be implemented in the future, check out http://wiki.dwoo.org/index.php/SmartySupport for more details.', E_USER_NOTICE);
            }

            return $this->compat['properties'][$p];
        } else {
            if ($this->show_compat_errors) {
                $this->triggerError('Property ' . $p . ' is not available in the Dwoo\Smarty\Adapter, but it is not listed as such, so you might want to tell me about it at j.boggiano@seld.be', E_USER_NOTICE);
            }
        }
    }

    /**
     * @param string $m
     * @param array  $a
     *
     * @return mixed|void
     */
    public function __call($m, $a)
    {
        if (method_exists($this->dataProvider, $m)) {
            call_user_func_array(
                array(
                $this->dataProvider,
                $m
                ), $a
            );
        } elseif ($this->show_compat_errors) {
            if (array_search($m, $this->compat['methods']) !== false) {
                $this->triggerError('Method ' . $m . ' is not available in the Dwoo\Smarty\Adapter, however it might be implemented in the future, check out http://wiki.dwoo.org/index.php/SmartySupport for more details.', E_USER_NOTICE);
            } else {
                $this->triggerError('Method ' . $m . ' is not available in the Dwoo\Smarty\Adapter, but it is not listed as such, so you might want to tell me about it at j.boggiano@seld.be', E_USER_NOTICE);
            }
        }
    }

    /**
     * List of unsupported properties and methods
     */
    protected $compat = array(
        'methods'    => array(
            'register_resource',
            'unregister_resource',
            'load_filter',
            'clear_compiled_tpl',
            'clear_config',
            'get_config_vars',
            'config_load',
        ),
        'properties' => array(
            'cache_handler_func'            => null,
            'debugging'                     => false,
            'error_reporting'               => null,
            'debugging_ctrl'                => 'NONE',
            'request_vars_order'            => 'EGPCS',
            'request_use_auto_globals'      => true,
            'use_sub_dirs'                  => false,
            'autoload_filters'              => array(),
            'default_template_handler_func' => '',
            'debug_tpl'                     => '',
            'cache_modified_check'          => false,
            'default_modifiers'             => array(),
            'default_resource_type'         => 'file',
            'config_overwrite'              => true,
            'config_booleanize'             => true,
            'config_read_hidden'            => false,
            'config_fix_newlines'           => true,
            'config_class'                  => 'Config_File',
        ),
    );

    /**
     * Security vars
     */
    public $security          = false;
    public $trusted_dir       = array();
    public $secure_dir        = array();
    public $php_handling      = SMARTY_PHP_PASSTHRU;
    public $security_settings = array(
        'PHP_HANDLING'    => false,
        'IF_FUNCS'        => array(
            'list',
            'empty',
            'count',
            'sizeof',
            'in_array',
            'is_array',
        ),
        'INCLUDE_ANY'     => false,
        'PHP_TAGS'        => false,
        'MODIFIER_FUNCS'  => array(),
        'ALLOW_CONSTANTS' => false,
    );

    /**
     * Paths
     */
    public $template_dir = 'templates';
    public $compile_dir  = 'templates_c';
    public $config_dir   = 'configs';
    public $cache_dir    = 'cache';
    public $plugins_dir  = array();

    /**
     * Misc options
     */
    public $left_delimiter  = '{';
    public $right_delimiter = '}';
    public $compile_check   = true;
    public $force_compile   = false;
    public $caching         = 0;
    public $cache_lifetime  = 3600;
    public $compile_id      = null;
    public $compiler_file   = null;
    public $compiler_class  = null;

    /**
     * Dwoo/Smarty compat layer
     */
    public           $show_compat_errors = false;
    protected        $dataProvider;
    protected        $_filters           = array(
        'pre'    => array(),
        'post'   => array(),
        'output' => array()
    );
    protected static $tplCache           = array();
    protected        $compiler           = null;

    /**
     * Adapter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->charset      = 'iso-8859-1';
        $this->dataProvider = new Data();
        $this->compiler     = new Compiler();
    }

    /**
     * @param      $filename
     * @param null $cacheId
     * @param null $compileId
     */
    public function display($filename, $cacheId = null, $compileId = null)
    {
        $this->fetch($filename, $cacheId, $compileId, true);
    }

    /**
     * @param      $filename
     * @param null $cacheId
     * @param null $compileId
     * @param bool $display
     *
     * @return string|void
     */
    public function fetch($filename, $cacheId = null, $compileId = null, $display = false)
    {
        $this->setCacheDir($this->cache_dir);
        $this->setCompileDir($this->compile_dir);

        if ($this->security) {
            $policy = new SecurityPolicy();
            $policy->addPhpFunction(array_merge($this->security_settings['IF_FUNCS'], $this->security_settings['MODIFIER_FUNCS']));

            $phpTags = $this->security_settings['PHP_HANDLING'] ? SMARTY_PHP_ALLOW : $this->php_handling;
            if ($this->security_settings['PHP_TAGS']) {
                $phpTags = SMARTY_PHP_ALLOW;
            }
            switch ($phpTags) {
            case SMARTY_PHP_ALLOW:
            case SMARTY_PHP_PASSTHRU:
                $phpTags = SecurityPolicy::PHP_ALLOW;
                break;
            case SMARTY_PHP_QUOTE:
                $phpTags = SecurityPolicy::PHP_ENCODE;
                break;
            case SMARTY_PHP_REMOVE:
            default:
                $phpTags = SecurityPolicy::PHP_REMOVE;
                break;
            }
            $policy->setPhpHandling($phpTags);

            $policy->setConstantHandling($this->security_settings['ALLOW_CONSTANTS']);

            if ($this->security_settings['INCLUDE_ANY']) {
                $policy->allowDirectory(preg_replace('{^((?:[a-z]:)?[\\\\/]).*}i', '$1', __FILE__));
            } else {
                $policy->allowDirectory($this->secure_dir);
            }

            $this->setSecurityPolicy($policy);
        }

        if (!empty($this->plugins_dir)) {
            foreach ($this->plugins_dir as $dir) {
                $this->getLoader()->addDirectory(rtrim($dir, '\\/'));
            }
        }

        $tpl = $this->makeTemplate($filename, $cacheId, $compileId);
        if ($this->force_compile) {
            $tpl->forceCompilation();
        }

        if ($this->caching > 0) {
            $this->cacheTime = $this->cache_lifetime;
        } else {
            $this->cacheTime = 0;
        }

        if ($this->compiler_class !== null) {
            if ($this->compiler_file !== null && !class_exists($this->compiler_class)) {
                include $this->compiler_file;
            }
            $this->compiler = new $this->compiler_class();
        } else {
            $this->compiler->addPreProcessor('PluginSmartyCompatible', true);
            $this->compiler->setLooseOpeningHandling(true);
        }

        $this->compiler->setDelimiters($this->left_delimiter, $this->right_delimiter);

        return $this->get($tpl, $this->dataProvider, $this->compiler, $display === true);
    }

    /**
     * @param mixed $_tpl
     * @param array $data
     * @param null  $_compiler
     * @param bool  $_output
     *
     * @return string|void
     */
    public function get($_tpl, $data = array(), $_compiler = null, $_output = false)
    {
        if ($_compiler === null) {
            $_compiler = $this->compiler;
        }

        return parent::get($_tpl, $data, $_compiler, $_output);
    }

    /**
     * @param      $name
     * @param      $callback
     * @param bool $cacheable
     * @param null $cache_attrs
     *
     * @throws Exception
     */
    public function register_function($name, $callback, $cacheable = true, $cache_attrs = null)
    {
        if (isset($this->plugins[$name]) && $this->plugins[$name][0] !== self::SMARTY_FUNCTION) {
            throw new Exception('Multiple plugins of different types can not share the same name');
        }
        $this->plugins[$name] = array(
            'type'     => self::SMARTY_FUNCTION,
            'callback' => $callback
        );
    }

    /**
     * @param $name
     */
    public function unregister_function($name)
    {
        unset($this->plugins[$name]);
    }

    /**
     * @param      $name
     * @param      $callback
     * @param bool $cacheable
     * @param null $cache_attrs
     *
     * @throws Exception
     */
    public function register_block($name, $callback, $cacheable = true, $cache_attrs = null)
    {
        if (isset($this->plugins[$name]) && $this->plugins[$name][0] !== self::SMARTY_BLOCK) {
            throw new Exception('Multiple plugins of different types can not share the same name');
        }
        $this->plugins[$name] = array(
            'type'     => self::SMARTY_BLOCK,
            'callback' => $callback
        );
    }

    /**
     * @param $name
     */
    public function unregister_block($name)
    {
        unset($this->plugins[$name]);
    }

    /**
     * @param $name
     * @param $callback
     *
     * @throws Exception
     */
    public function register_modifier($name, $callback)
    {
        if (isset($this->plugins[$name]) && $this->plugins[$name][0] !== self::SMARTY_MODIFIER) {
            throw new Exception('Multiple plugins of different types can not share the same name');
        }
        $this->plugins[$name] = array(
            'type'     => self::SMARTY_MODIFIER,
            'callback' => $callback
        );
    }

    /**
     * @param $name
     */
    public function unregister_modifier($name)
    {
        unset($this->plugins[$name]);
    }

    /**
     * @param $callback
     */
    public function register_prefilter($callback)
    {
        $processor = new ProcessorAdapter($this->compiler);
        $processor->registerCallback($callback);
        $this->_filters['pre'][] = $processor;
        $this->compiler->addPreProcessor($processor);
    }

    /**
     * @param $callback
     */
    public function unregister_prefilter($callback)
    {
        foreach ($this->_filters['pre'] as $index => $processor) {
            if ($processor->callback === $callback) {
                $this->compiler->removePostProcessor($processor);
                unset($this->_filters['pre'][$index]);
            }
        }
    }

    /**
     * @param $callback
     */
    public function register_postfilter($callback)
    {
        $processor = new ProcessorAdapter($this->compiler);
        $processor->registerCallback($callback);
        $this->_filters['post'][] = $processor;
        $this->compiler->addPostProcessor($processor);
    }

    /**
     * @param $callback
     */
    public function unregister_postfilter($callback)
    {
        foreach ($this->_filters['post'] as $index => $processor) {
            if ($processor->callback === $callback) {
                $this->compiler->removePostProcessor($processor);
                unset($this->_filters['post'][$index]);
            }
        }
    }

    /**
     * @param $callback
     */
    public function register_outputfilter($callback)
    {
        $filter = new FilterAdapter($this);
        $filter->registerCallback($callback);
        $this->_filters['output'][] = $filter;
        $this->addFilter($filter);
    }

    /**
     * @param $callback
     */
    public function unregister_outputfilter($callback)
    {
        foreach ($this->_filters['output'] as $index => $filter) {
            if ($filter->callback === $callback) {
                $this->removeOutputFilter($filter);
                unset($this->_filters['output'][$index]);
            }
        }
    }

    /**
     * @param       $object
     * @param       $object_impl
     * @param array $allowed
     * @param bool  $smarty_args
     * @param array $block_methods
     */
    public function register_object($object, $object_impl, $allowed = array(), $smarty_args = false, $block_methods = array())
    {
        settype($allowed, 'array');
        settype($block_methods, 'array');
        settype($smarty_args, 'boolean');

        if (!empty($allowed) && $this->show_compat_errors) {
            $this->triggerError('You can register objects but can not restrict the method/properties used, this is PHP5, you have proper OOP access restrictions so use them.', E_USER_NOTICE);
        }

        if ($smarty_args) {
            $this->triggerError('You can register objects but methods will be called using method($arg1, $arg2, $arg3), not as an argument array like smarty did.', E_USER_NOTICE);
        }

        if (!empty($block_methods)) {
            $this->triggerError('You can register objects but can not use methods as being block methods, you have to build a plugin for that.', E_USER_NOTICE);
        }

        $this->dataProvider->assign($object, $object_impl);
    }

    /**
     * @param $object
     */
    public function unregister_object($object)
    {
        $this->dataProvider->clear($object);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function get_registered_object($name)
    {
        $data = $this->dataProvider->getData();
        if (isset($data[$name]) && is_object($data[$name])) {
            return $data[$name];
        } else {
            trigger_error('Dwoo_Compiler: object "' . $name . '" was not registered or is not an object', E_USER_ERROR);
        }
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    public function template_exists($filename)
    {
        if (!is_array($this->template_dir)) {
            return file_exists($this->template_dir . DIRECTORY_SEPARATOR . $filename);
        } else {
            foreach ($this->template_dir as $tpl_dir) {
                if (file_exists($tpl_dir . DIRECTORY_SEPARATOR . $filename)) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * @param      $tpl
     * @param null $cacheId
     * @param null $compileId
     *
     * @return bool
     */
    public function is_cached($tpl, $cacheId = null, $compileId = null)
    {
        return $this->isCached($this->makeTemplate($tpl, $cacheId, $compileId));
    }

    /**
     * @param      $var
     * @param      $value
     * @param bool $merge
     */
    public function append_by_ref($var, &$value, $merge = false)
    {
        $this->dataProvider->appendByRef($var, $value, $merge);
    }

    /**
     * @param $name
     * @param $val
     */
    public function assign_by_ref($name, &$val)
    {
        $this->dataProvider->assignByRef($name, $val);
    }

    /**
     * @param $var
     */
    public function clear_assign($var)
    {
        $this->dataProvider->clear($var);
    }

    /**
     *
     */
    public function clear_all_assign()
    {
        $this->dataProvider->clear();
    }

    /**
     * @param null $name
     *
     * @return array|null
     */
    public function get_template_vars($name = null)
    {
        if ($this->show_compat_errors) {
            trigger_error('get_template_vars does not return values by reference, if you try to modify the data that way you should modify your code.', E_USER_NOTICE);
        }

        $data = $this->dataProvider->getData();
        if ($name === null) {
            return $data;
        } elseif (isset($data[$name])) {
            return $data[$name];
        }

        return null;
    }

    /**
     * @param int $olderThan
     */
    public function clear_all_cache($olderThan = 0)
    {
        $this->clearCache($olderThan);
    }

    /**
     * @param      $template
     * @param null $cacheId
     * @param null $compileId
     * @param int  $olderThan
     */
    public function clear_cache($template, $cacheId = null, $compileId = null, $olderThan = 0)
    {
        $this->makeTemplate($template, $cacheId, $compileId)->clearCache($olderThan);
    }

    /**
     * @param     $error_msg
     * @param int $error_type
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        $this->triggerError($error_msg, $error_type);
    }

    /**
     *
     */
    protected function initGlobals()
    {
        parent::initGlobals();
        $this->globals['ldelim'] = '{';
        $this->globals['rdelim'] = '}';
    }

    /**
     * @param $file
     * @param $cacheId
     * @param $compileId
     *
     * @return mixed
     * @throws Exception
     */
    protected function makeTemplate($file, $cacheId, $compileId)
    {
        if ($compileId === null) {
            $compileId = $this->compile_id;
        }

        $hash = bin2hex(md5($file . $cacheId . $compileId, true));
        if (!isset(self::$tplCache[$hash])) {
            // abs path
            if (substr($file, 0, 1) === '/' || substr($file, 1, 1) === ':') {
                self::$tplCache[$hash] = new TemplateFile($file, null, $cacheId, $compileId);
            } elseif (is_string($this->template_dir) || is_array($this->template_dir)) {
                self::$tplCache[$hash] = new TemplateFile($file, null, $cacheId, $compileId, $this->template_dir);
            } else {
                throw new Exception('Unable to load "' . $file . '", check the template_dir');
            }
        }

        return self::$tplCache[$hash];
    }

    /**
     * @param string $message
     * @param int    $level
     */
    public function triggerError($message, $level = E_USER_NOTICE)
    {
        if (is_object($this->template)) {
            parent::triggerError($message, $level);
        }
        trigger_error('Dwoo error : ' . $message, $level);
    }
}
