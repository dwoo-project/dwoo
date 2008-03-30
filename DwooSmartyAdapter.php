<?php

if(!defined('DIR_SEP')) {
    define('DIR_SEP', DIRECTORY_SEPARATOR);
}

if(!defined('SMARTY_PHP_PASSTHRU'))
{
	define('SMARTY_PHP_PASSTHRU',   0);
	define('SMARTY_PHP_QUOTE',      1);
	define('SMARTY_PHP_REMOVE',     2);
	define('SMARTY_PHP_ALLOW',      3);
}

if(class_exists('DwooCompiler', false) === false)
	require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DwooCompiler.php';

/**
 * A Smarty compatibility layer for Dwoo
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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooSmarty_Adapter extends Dwoo
{
	public function __set($p, $v)
	{
		if(array_key_exists($p, $this->compat['properties']) !== false)
		{
			if($this->show_compat_errors)
				$this->triggerError('Property '.$p.' is not available in the DwooSmartyAdapter, however it might be implemented in the future, check out http://wiki.dwoo.org/index.php/SmartySupport for more details.', E_USER_NOTICE);
			$this->compat['properties'][$p] = $v;
		}
		else
		{
			if($this->show_compat_errors)
				$this->triggerError('Property '.$p.' is not available in the DwooSmartyAdapter, but it is not listed as such, so you might want to tell me about it at j.boggiano@seld.be', E_USER_NOTICE);
		}
	}

	public function __get($p)
	{
		if(array_key_exists($p, $this->compat['properties']) !== false)
		{
			if($this->show_compat_errors)
				$this->triggerError('Property '.$p.' is not available in the DwooSmartyAdapter, however it might be implemented in the future, check out http://wiki.dwoo.org/index.php/SmartySupport for more details.', E_USER_NOTICE);
			return $this->compat['properties'][$p];
		}
		else
		{
			if($this->show_compat_errors)
				$this->triggerError('Property '.$p.' is not available in the DwooSmartyAdapter, but it is not listed as such, so you might want to tell me about it at j.boggiano@seld.be', E_USER_NOTICE);
		}
	}

	public function __call($m, $a)
	{
		if(method_exists($this->dataProvider, $m))
			call_user_func_array(array($this->dataProvider, $m), $a);
		elseif($this->show_compat_errors)
		{
			if(array_search($m, $this->compat['methods']) !== false)
				$this->triggerError('Method '.$m.' is not available in the DwooSmartyAdapter, however it might be implemented in the future, check out http://wiki.dwoo.org/index.php/SmartySupport for more details.', E_USER_NOTICE);
			else
				$this->triggerError('Method '.$m.' is not available in the DwooSmartyAdapter, but it is not listed as such, so you might want to tell me about it at j.boggiano@seld.be', E_USER_NOTICE);
		}
	}

	protected $compat = array
	(
		'methods' => array
		(
			'register_resource', 'unregister_resource', 'load_filter',
			'register_object', 'unregister_object', 'get_registered_object',
			'clear_compiled_tpl',
			'clear_config', 'get_config_vars', 'config_load'
		),
		'properties' => array
		(
			'debugging' => false,
			'error_reporting' => null,
			'debugging_ctrl' => 'NONE',
			'request_vars_order' => 'EGPCS',
			'request_use_auto_globals' => true,
			'use_sub_dirs' => false,
			'default_resource_type' => 'file',
			'cache_handler_func' => null,
			'autoload_filters' => array(),
			'default_template_handler_func' => '',
			'debug_tpl' => '',
			'trusted_dir' => array(),
			'cache_modified_check' => false,
			'secure_dir' => array(),
			'security_settings' => array(
			                                    'PHP_HANDLING'    => false,
			                                    'IF_FUNCS'        => array('array', 'list',
			                                                               'isset', 'empty',
			                                                               'count', 'sizeof',
			                                                               'in_array', 'is_array',
			                                                               'true', 'false', 'null'),
			                                    'INCLUDE_ANY'     => false,
			                                    'PHP_TAGS'        => false,
			                                    'MODIFIER_FUNCS'  => array('count'),
			                                    'ALLOW_CONSTANTS'  => false
			                                   ),
			'default_modifiers' => array(),
			'config_overwrite' => true,
			'config_booleanize' => true,
			'config_read_hidden' => false,
			'config_fix_newlines' => true,
			'config_class' => 'Config_File',
			'php_handling' => SMARTY_PHP_PASSTHRU,
			'security' => false
		),
	);

	public $template_dir = 'templates';
	public $compile_dir = 'templates_c';
	public $config_dir = 'configs';
	public $cache_dir = 'cache';
	public $plugins_dir = array();
	public $left_delimiter = '{';
	public $right_delimiter = '}';

	public $compile_check = true;
	public $force_compile = false;
	public $caching = 0;
	public $cache_lifetime = 3600;
	public $compile_id = null;

	public $compiler_file = null;
	public $compiler_class = null;

	public $show_compat_errors = false;
	protected $dataProvider;
	protected $_filters = array('pre'=>array(), 'post'=>array(), 'output'=>array());
	protected static $tplCache = array();
	protected $compiler;

	public function __construct()
	{
		parent::__construct();
		$this->charset = 'iso-8859-1';
		$this->dataProvider = new DwooData();
		$this->compiler = new DwooCompiler();
		$this->compiler->smartyCompat = true;
	}

	public function display($filename, $cacheId=null, $compileId=null)
	{
		$this->fetch($filename, $cacheId, $compileId, true);
	}

	public function fetch($filename, $cacheId=null, $compileId=null, $display=false)
	{
		if(!empty($this->plugins_dir))
			foreach($this->plugins_dir as $dir)
				DwooLoader::addDirectory(rtrim($dir, '\\/'));

		$tpl = $this->makeTemplate($filename, $cacheId, $compileId);
		if($this->force_compile)
			$tpl->forceCompilation();

		if($this->caching > 0)
			$this->cacheTime = $this->cache_lifetime;
		else
			$this->cacheTime = 0;

		if($this->compiler_class !== null)
		{
			if($this->compiler_file !== null && !class_exists($this->compiler_class, false))
				include $this->compiler_file;
			$this->compiler = new $this->compiler_class;
		}

		$this->compiler->setDelimiters($this->left_delimiter, $this->right_delimiter);

		return $this->output($tpl, $this->dataProvider, $this->compiler, $display===false);
	}

    public function register_function($name, $callback, $cacheable=true, $cache_attrs=null)
    {
    	if(isset($this->plugins[$name]) && $this->plugins[$name][0] !== self::SMARTY_FUNCTION)
    		throw new Exception('Multiple plugins of different types can not share the same name');
		$this->plugins[$name] = array('type'=>self::SMARTY_FUNCTION, 'callback'=>$callback);
    }

    public function unregister_function($name)
    {
        unset($this->plugins[$name]);
    }

    public function register_block($name, $callback, $cacheable=true, $cache_attrs=null)
    {
    	if(isset($this->plugins[$name]) && $this->plugins[$name][0] !== self::SMARTY_BLOCK)
    		throw new Exception('Multiple plugins of different types can not share the same name');
		$this->plugins[$name] = array('type'=>self::SMARTY_BLOCK, 'callback'=>$callback);
    }

    public function unregister_block($name)
    {
        unset($this->plugins[$name]);
    }

    public function register_modifier($name, $callback)
    {
    	if(isset($this->plugins[$name]) && $this->plugins[$name][0] !== self::SMARTY_MODIFIER)
    		throw new Exception('Multiple plugins of different types can not share the same name');
		$this->plugins[$name] = array('type'=>self::SMARTY_MODIFIER, 'callback'=>$callback);
    }

    public function unregister_modifier($name)
    {
        unset($this->plugins[$name]);
    }

    public function register_prefilter($callback)
    {
    	$processor = new DwooSmartyProcessorAdapter($this->compiler);
    	$processor->registerCallback($callback);
    	$this->_filters['pre'][] = $processor;
    	$this->compiler->addPreProcessor($processor);
    }

    public function unregister_prefilter($callback)
    {
    	foreach($this->_filters['pre'] as $index => $processor)
	    	if($processor->callback === $callback)
	    	{
	    		$this->compiler->removePostProcessor($processor);
	    		unset($this->_filters['pre'][$index]);
	    	}
    }

    public function register_postfilter($callback)
    {
    	$processor = new DwooSmartyProcessorAdapter($this->compiler);
    	$processor->registerCallback($callback);
    	$this->_filters['post'][] = $processor;
    	$this->compiler->addPostProcessor($processor);
    }

    public function unregister_postfilter($callback)
    {
    	foreach($this->_filters['post'] as $index => $processor)
	    	if($processor->callback === $callback)
	    	{
	    		$this->compiler->removePostProcessor($processor);
	    		unset($this->_filters['post'][$index]);
	    	}
    }

    public function register_outputfilter($callback)
    {
    	$filter = new DwooSmartyFilterAdapter($this);
    	$filter->registerCallback($callback);
    	$this->_filters['output'][] = $filter;
    	$this->addFilter($filter);
    }

    public function unregister_outputfilter($callback)
    {
    	foreach($this->_filters['output'] as $index => $filter)
	    	if($filter->callback === $callback)
	    	{
	    		$this->removeOutputFilter($filter);
	    		unset($this->_filters['output'][$index]);
	    	}
    }

	public function template_exists($filename)
	{
		return file_exists($this->template_dir.DIRECTORY_SEPARATOR.$filename);
	}

   	public function is_cached($tpl, $cacheId = null, $compileId = null)
   	{
   		return $this->isCached($this->makeTemplate($tpl, $cacheId, $compileId));
   	}

   	public function append_by_ref($var, &$value, $merge=false)
   	{
   		$this->dataProvider->appendByRef($var, $value, $merge);
   	}

	public function assign_by_ref($name, &$val)
	{
		$this->dataProvider->assignByRef($name, $val);
	}

   	public function clear_assign($var)
   	{
   		$this->dataProvider->clear($var);
   	}

   	public function clear_all_assign()
   	{
   		$this->dataProvider->clear();
   	}

	public function get_template_vars($name=null)
	{
		if($this->show_compat_errors)
			trigger_error('get_template_vars does not return values by reference, if you try to modify the data that way you should modify your code.', E_USER_NOTICE);

		$data = $this->dataProvider->getData();
   		if($name === null)
   			return $data;
   		elseif(isset($data[$name]))
   			return $data[$name];
   		return null;
   	}

   	public function clear_all_cache($olderThan = 0)
   	{
   		$this->clearCache($olderThan);
   	}

   	public function clear_cache($template, $cacheId = null, $compileId = null, $olderThan = 0)
   	{
   		$this->makeTemplate($template, $cacheId, $compileId)->clearCache($olderThan);
   	}

    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
	{
		$this->triggerError($error_msg, $error_type);
	}

	protected function initGlobals(DwooITemplate $tpl)
	{
		parent::initGlobals($tpl);
		$this->globals['ldelim'] = '{';
		$this->globals['rdelim'] = '}';
	}

	protected function makeTemplate($file, $cacheId, $compileId)
	{
		$this->setCacheDir($this->cache_dir);
		$this->setCompileDir($this->compile_dir);

   		if($compileId === null)
   			$compileId = $this->compile_id;

		$hash = bin2hex(md5($file.$cacheId.$compileId, true));
		if(!isset(self::$tplCache[$hash]))
			self::$tplCache[$hash] = new DwooTemplateFile($this->template_dir.DIRECTORY_SEPARATOR.$file, null, $cacheId, $compileId);
		return self::$tplCache[$hash];
	}
}

class DwooSmartyFilterAdapter extends DwooFilter
{
	public $callback;

	public function process($input)
	{
		return call_user_func($this->callback, $input);
	}

	public function registerCallback($callback)
	{
		$this->callback = $callback;
	}
}

class DwooSmartyProcessorAdapter extends DwooProcessor
{
	public $callback;

	public function process($input)
	{
		return call_user_func($this->callback, $input);
	}

	public function registerCallback($callback)
	{
		$this->callback = $callback;
	}
}

// cloaks the adapter if possible with the smarty name to fool type-hinted plugins
if(class_exists('Smarty', false) === false)
{
	interface Smarty {}
	class DwooSmartyAdapter extends DwooSmarty_Adapter implements Smarty {}
}
else
{
	class DwooSmartyAdapter extends DwooSmarty_Adapter {}
}

?>