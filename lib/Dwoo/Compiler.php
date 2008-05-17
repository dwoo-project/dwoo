<?php

include 'Dwoo/Compilation/Exception.php';

/**
 * default dwoo compiler class, compiles dwoo templates into php
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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
class Dwoo_Compiler implements Dwoo_ICompiler
{
	/**
	 * constant that represents a php opening tag
	 *
	 * use it in case it needs to be adjusted
	 *
	 * @var string
	 */
	const PHP_OPEN = "<?php ";

	/**
	 * constant that represents a php closing tag
	 *
	 * use it in case it needs to be adjusted
	 *
	 * @var string
	 */
	const PHP_CLOSE = "?>";

	/**
	 * boolean flag to enable or disable debugging output
	 *
	 * @var bool
	 */
	public $debug = false;

	/**
	 * left script delimiter
	 *
	 * @var string
	 */
	protected $ld = '{';

	/**
	 * left script delimiter with escaped regex meta characters
	 *
	 * @var string
	 */
	protected $ldr = '\\{';

	/**
	 * right script delimiter
	 *
	 * @var string
	 */
	protected $rd = '}';

	/**
	 * right script delimiter with escaped regex meta characters
	 *
	 * @var string
	 */
	protected $rdr = '\\}';

	/**
	 * defines whether opening and closing tags can contain spaces before valid data or not
	 *
	 * turn to true if you want to be sloppy with the syntax, but when set to false it allows
	 * to skip javascript and css tags as long as they are in the form "{ something", which is
	 * nice. default is false.
	 */
	protected $allowLooseOpenings = false;

	/**
	 * security policy object
	 *
	 * @var Dwoo_Security_Policy
	 */
	protected $securityPolicy;

	/**
	 * storage for parse errors/warnings
	 *
	 * will be deprecated when proper exceptions are added
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * stores the custom plugins registered with this compiler
	 *
	 * @var array
	 */
	protected $customPlugins = array();

	/**
	 * stores the pre- and post-processors callbacks
	 *
	 * @var array
	 */
	protected $processors = array('pre'=>array(), 'post'=>array());

	/**
	 * stores a list of plugins that are used in the currently compiled
	 * template, and that are not compilable. these plugins will be loaded
	 * during the template's runtime if required.
	 *
	 * it is a 1D array formatted as key:pluginName value:pluginType
	 *
	 * @var array
	 */
	protected $usedPlugins;

	/**
	 * stores the template undergoing compilation
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * stores the current pointer position inside the template
	 *
	 * @var int
	 */
	protected $pointer;

	/**
	 * stores the data within which the scope moves
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * variable scope of the compiler, set to null if
	 * it can not be resolved to a static string (i.e. if some
	 * plugin defines a new scope based on a variable array key)
	 *
	 * @var mixed
	 */
	protected $scope;

	/**
	 * variable scope tree, that allows to rebuild the current
	 * scope if required, i.e. when going to a parent level
	 *
	 * @var array
	 */
	protected $scopeTree;

	/**
	 * block plugins stack, accessible through some methods
	 *
	 * @see findBlock
	 * @see getCurrentBlock
	 * @see addBlock
	 * @see addCustomBlock
	 * @see injectBlock
	 * @see removeBlock
	 * @see removeTopBlock
	 *
	 * @var array
	 */
	protected $stack = array();

	/**
	 * current block at the top of the block plugins stack,
	 * accessible through getCurrentBlock
	 *
	 * @see getCurrentBlock
	 *
	 * @var Dwoo_Block_Plugin
	 */
	protected $curBlock;

	/**
	 * holds an instance of this class, used by getInstance when you don't
	 * provide a custom compiler in order to save resources
	 *
	 * @var Dwoo_Compiler
	 */
	protected static $instance;

	/**
	 * sets the delimiters to use in the templates
	 *
	 * delimiters can be multi-character strings but should not be one of those as they will
	 * make it very hard to work with templates or might even break the compiler entirely : "\", "$", "|", ":" and finally "#" only if you intend to use config-vars with the #var# syntax.
	 *
	 * @param string $left left delimiter
	 * @param string $right right delimiter
	 */
	public function setDelimiters($left, $right)
	{
		$this->ld = $left;
		$this->rd = $right;
		$this->ldr = preg_quote($left);
		$this->rdr = preg_quote($right);
	}

	/**
	 * returns the left and right template delimiters
	 *
	 * @return array containing the left and the right delimiters
	 */
	public function getDelimiters()
	{
		return array($this->ld, $this->rd);
	}

	/**
	 * sets the tag openings handling strictness, if set to true, template tags can
	 * contain spaces before the first function/string/variable such as { $foo} is valid.
	 *
	 * if set to false (default setting), { $foo} is invalid but that is however a good thing
	 * as it allows css (i.e. #foo { color:red; }) to be parsed silently without triggering
	 * an error, same goes for javascript.
	 *
	 * @param bool $allow true to allow loose handling, false to restore default setting
	 */
	public function setLooseOpeningHandling($allow = false)
	{
		$this->allowLooseOpenings = (bool) $allow;
	}

	/**
	 * returns the tag openings handling strictness setting
	 *
	 * @see setLooseOpeningHandling
	 * @return bool true if loose tags are allowed
	 */
	public function getLooseOpeningHandling()
	{
		return $this->allowLooseOpenings;
	}

	/**
	 * adds a preprocessor to the compiler, it will be called
	 * before the template is compiled
	 *
	 * @param mixed $callback either a valid callback to the preprocessor or a simple name if the autoload is set to true
	 * @param bool $autoload if set to true, the preprocessor is auto-loaded from one of the plugin directories, else you must provide a valid callback
	 */
	public function addPreProcessor($callback, $autoload = false)
	{
		if($autoload)
		{
			$name = str_replace('Dwoo_Processor_', '', $callback);
			$class = 'Dwoo_Processor_'.$name;

			if(!class_exists($class, false) && !function_exists($class))
				Dwoo_Loader::loadPlugin($name);

			if(class_exists($class, false))
				$callback = array(new $class($this), 'process');
			elseif(function_exists($class))
				$callback = $class;
			else
				throw new Dwoo_Exception('Wrong pre-processor name, when using autoload the filter must be in one of your plugin dir as "name.php" containg a class or function named "Dwoo_Processor_name"');

			$this->processors['pre'][] = $callback;
		}
		else
		{
			$this->processors['pre'][] = $callback;
		}
	}

	/**
	 * removes a preprocessor from the compiler
	 *
	 * @param mixed $callback either a valid callback to the preprocessor or a simple name if it was autoloaded
	 */
	public function removePreProcessor($callback)
	{
		if(($index = array_search($callback, $this->processors['pre'], true)) !== false)
			unset($this->processors['pre'][$index]);
		elseif(($index = array_search('Dwoo_Processor_'.str_replace('Dwoo_Processor_', '', $callback), $this->processors['pre'], true)) !== false)
			unset($this->processors['pre'][$index]);
		else
		{
			$class = 'Dwoo_Processor_' . str_replace('Dwoo_Processor_', '', $callback);
			foreach($this->processors['pre'] as $index=>$proc)
			{
				if(is_array($proc) && $proc[0] instanceof $class)
				{
					unset($this->processors['pre'][$index]);
					break;
				}
			}
		}
	}

	/**
	 * adds a postprocessor to the compiler, it will be called
	 * before the template is compiled
	 *
	 * @param mixed $callback either a valid callback to the postprocessor or a simple name if the autoload is set to true
	 * @param bool $autoload if set to true, the postprocessor is auto-loaded from one of the plugin directories, else you must provide a valid callback
	 */
	public function addPostProcessor($callback, $autoload = false)
	{
		if($autoload)
		{
			$name = str_replace('Dwoo_Processor_', '', $callback);
			$class = 'Dwoo_Processor_'.$name;

			if(!class_exists($class, false) && !function_exists($class))
				Dwoo_Loader::loadPlugin($name);

			if(class_exists($class, false))
				$callback = array(new $class($this), 'process');
			elseif(function_exists($class))
				$callback = $class;
			else
				throw new Dwoo_Exception('Wrong post-processor name, when using autoload the processor must be in one of your plugin dir as "name.php" containg a class or function named "Dwoo_Processor_name"');

			$this->processors['post'][] = $callback;
		}
		else
		{
			$this->processors['post'][] = $callback;
		}
	}

	/**
	 * removes a postprocessor from the compiler
	 *
	 * @param mixed $callback either a valid callback to the postprocessor or a simple name if it was autoloaded
	 */
	public function removePostProcessor($callback)
	{
		if(($index = array_search($callback, $this->processors['post'], true)) !== false)
			unset($this->processors['post'][$index]);
		elseif(($index = array_search('Dwoo_Processor_'.str_replace('Dwoo_Processor_', '', $callback), $this->processors['post'], true)) !== false)
			unset($this->processors['post'][$index]);
		else
		{
			$class = 'Dwoo_Processor_' . str_replace('Dwoo_Processor_', '', $callback);
			foreach($this->processors['post'] as $index=>$proc)
			{
				if(is_array($proc) && $proc[0] instanceof $class)
				{
					unset($this->processors['post'][$index]);
					break;
				}
			}
		}
	}

	/**
	 * adds the custom plugins loaded into Dwoo to the compiler so it can load them
	 *
	 * @see Dwoo::addPlugin
	 * @param array $customPlugins an array of custom plugins
	 */
	public function setCustomPlugins(array $customPlugins)
	{
		$this->customPlugins = $customPlugins;
	}

	/**
	 * sets the security policy object to enforce some php security settings
	 *
	 * use this if untrusted persons can modify templates,
	 * set it on the Dwoo object as it will be passed onto the compiler automatically
	 *
	 * @param Dwoo_Security_Policy $policy the security policy object
	 */
	public function setSecurityPolicy(Dwoo_Security_Policy $policy = null)
	{
		$this->securityPolicy = $policy;
	}

	/**
	 * returns the current security policy object or null by default
	 *
	 * @return Dwoo_Security_Policy|null the security policy object if any
	 */
	public function getSecurityPolicy()
	{
		return $this->securityPolicy;
	}

	/**
	 * sets the pointer position
	 *
	 * @param int $position the new pointer position
	 * @param bool $isOffset if set to true, the position acts as an offset and not an absolute position
	 */
	public function setPointer($position, $isOffset = false)
	{
		if($isOffset)
			$this->pointer += $position;
		else
			$this->pointer = $position;
	}

	/**
	 * returns the current pointer position, only available during compilation of a template
	 *
	 * @return int
	 */
	public function getPointer()
	{
		return $this->pointer;
	}

	/**
	 * returns the dwoo object that initiated this template compilation, only available during compilation of a template
	 *
	 * @return Dwoo
	 */
	public function getDwoo()
	{
		return $this->dwoo;
	}

	/**
	 * overwrites the template that is being compiled
	 *
	 * @param string $newSource the template source that must replace the current one
	 * @param bool $fromPointer if set to true, only the source from the current pointer position is replaced
	 * @return string the template or partial template
	 */
	public function setTemplateSource($newSource, $fromPointer = false)
	{
		if($fromPointer === true)
			$this->templateSource = substr($this->templateSource, 0, $this->pointer) . $newSource;
		else
			$this->templateSource = $newSource;
	}

	/**
	 * returns the template that is being compiled
	 *
	 * @param bool $fromPointer if set to true, only the source from the current pointer position is returned
	 * @return string the template or partial template
	 */
	public function getTemplateSource($fromPointer = false)
	{
		if($fromPointer)
			return substr($this->templateSource, $this->pointer);
		else
			return $this->templateSource;
	}

	/**
	 * compiles the provided string down to php code
	 *
	 * @param string $tpl the template to compile
	 * @return string a compiled php string
	 */
	public function compile(Dwoo $dwoo, Dwoo_ITemplate $template)
	{
		// init vars
		$tpl = $template->getSource();
		$ptr = 0;
		$this->dwoo = $dwoo;
		$this->template = $template;
		$this->templateSource =& $tpl;
		$this->pointer =& $ptr;

		if($this->debug) echo 'PROCESSING PREPROCESSORS<br>';

		// runs preprocessors
		foreach($this->processors['pre'] as $preProc)
		{
			if(is_array($preProc) && $preProc[0] instanceof Dwoo_Processor)
				$tpl = call_user_func($preProc, $tpl);
			else
				$tpl = call_user_func($preProc, $this, $tpl);
		}
		unset($preProc);

		if($this->debug) echo '<pre>'.print_r(htmlentities($tpl), true).'<hr>';

		// strips comments
		if(strstr($tpl, $this->ld.'*') !== false)
			$tpl = preg_replace('/'.$this->ldr.'\*.*?\*'.$this->rdr.'/s', '', $tpl);

		// strips php tags if required by the security policy
		if($this->securityPolicy !== null)
		{
			$search = array('{<\?php.*?\?>}');
			if(ini_get('short_open_tags'))
				$search = array('{<\?.*?\?>}', '{<%.*?%>}');
			switch($this->securityPolicy->getPhpHandling())
			{
				case Dwoo_Security_Policy::PHP_ALLOW:
					break;
				case Dwoo_Security_Policy::PHP_ENCODE:
					$tpl = preg_replace_callback($search, array($this, 'phpTagEncodingHelper'), $tpl);
					break;
				case Dwoo_Security_Policy::PHP_REMOVE:
					$tpl = preg_replace($search, '', $tpl);
			}
		}

		// handles the built-in strip function
		if(preg_match('/'.$this->ldr . ($this->allowLooseOpenings ? '\s*' : '') . 'strip' . ($this->allowLooseOpenings ? '\s*' : '') . $this->rdr.'/s', $tpl, $pos, PREG_OFFSET_CAPTURE) && substr($tpl, $pos[0][1]-1, 1) !== '\\')
		{
			if(!preg_match('/'.$this->ldr . ($this->allowLooseOpenings ? '\s*' : '') . '\/strip' . ($this->allowLooseOpenings ? '\s*' : '') . $this->rdr.'/s', $tpl))
				throw new Dwoo_Compilation_Exception('The {strip} blocks must be closed explicitly');
			$tpl = preg_replace_callback('/'.$this->ldr.($this->allowLooseOpenings ? '\s*' : '').'strip'.($this->allowLooseOpenings ? '\s*' : '').$this->rdr.'(.+?)'.$this->ldr.($this->allowLooseOpenings ? '\s*' : '').'\/strip'.($this->allowLooseOpenings ? '\s*' : '').$this->rdr.'/s', array($this, 'stripPreprocessorHelper'), $tpl);
		}

		while(true)
		{
			// if pointer is at the beginning, reset everything, that allows a plugin to externally reset the compiler if everything must be reparsed
			if($ptr===0)
			{
				// resets variables
				$this->usedPlugins = array('topLevelBlock' => Dwoo::BLOCK_PLUGIN);
				$this->data = array();
				$this->scope =& $this->data;
				$this->scopeTree = array();
				$this->stack = array();
				// add top level block
				$compiled = $this->addBlock('topLevelBlock', array(), 0);
			}

			$pos = strpos($tpl, $this->ld, $ptr);

			if($pos === false)
			{
				$compiled .= substr($tpl, $ptr);
				break;
			}
			elseif(substr($tpl, $pos-1, 1) === '\\' && substr($tpl, $pos-2, 1) !== '\\')
			{
				$compiled .= substr($tpl, $ptr, $pos-$ptr-1).$this->ld;
				$ptr = $pos+strlen($this->ld);
			}
			elseif(preg_match('/^'.$this->ldr . ($this->allowLooseOpenings ? '\s*' : '') . 'literal' . ($this->allowLooseOpenings ? '\s*' : '') . $this->rdr.'/s', substr($tpl, $pos), $litOpen))
			{
				if(!preg_match('/'.$this->ldr . ($this->allowLooseOpenings ? '\s*' : '') . '\/literal' . ($this->allowLooseOpenings ? '\s*' : '') . $this->rdr.'/s', $tpl, $litClose, PREG_OFFSET_CAPTURE, $pos))
					throw new Dwoo_Compilation_Exception('The {literal} blocks must be closed explicitly');
				$endpos = $litClose[0][1];
				$compiled .= substr($tpl, $ptr, $pos-$ptr) . substr($tpl, $pos + strlen($litOpen[0]), $endpos-$pos-strlen($litOpen[0]));
				$ptr = $endpos+strlen($litClose[0][0]);
			}
			else
			{
				if(substr($tpl, $pos-2, 1) === '\\' && substr($tpl, $pos-1, 1) === '\\')
				{
					$compiled .= substr($tpl, $ptr, $pos-$ptr-1);
					$ptr = $pos;
				}

				$compiled .= substr($tpl, $ptr, $pos-$ptr);

				$endpos = strpos($tpl, $this->rd, $pos);

				if($endpos===false)
					throw new Dwoo_Compilation_Exception('A template tag was not closed, started with <em>'.substr($tpl, $pos, 100).'</em>');

				while(substr($tpl, $endpos-1, 1) === '\\')
				{
					$tpl = substr_replace($tpl, $this->rd, $endpos-1, 1+strlen($this->rd));
					$endpos = strpos($tpl, $this->rd, $endpos);
				}

				$pos += strlen($this->ld);
				if($this->allowLooseOpenings)
				{
					while(substr($tpl, $pos, 1) === ' ')
						$pos+=1;
				}
				else
				{
					if(substr($tpl, $pos, 1) === ' ' || substr($tpl, $pos, 1) === "\r" || substr($tpl, $pos, 1) === "\n")
					{
						$ptr = $pos;
						$compiled .= $this->ld;
						continue;
					}
				}

				$ptr = $endpos+strlen($this->rd);

				if(substr($tpl, $pos, 1)==='/')
				{
					if(substr($tpl, $pos, $endpos-$pos) === '/')
						$compiled .= $this->removeTopBlock();
					else
						$compiled .= $this->removeBlock(substr($tpl, $pos+1, $endpos-$pos-1));
				}
				else
					$compiled .= $this->parse($tpl, $pos, $endpos, false, 'root');

				// adds additional line breaks between php closing and opening tags because the php parser removes those if there is just a single line break
				if(substr($compiled, -2) === '?>' && preg_match('{^(([\r\n])([\r\n]?))}', substr($tpl, $ptr, 3), $m))
				{
					if($m[3] === '')
					{
						$ptr+=1;
						$compiled .= $m[1].$m[1];
					}
					else
					{
						$ptr+=2;
						$compiled .= $m[1]."\n";
					}
				}
			}
		}

		$compiled .= $this->removeBlock('topLevelBlock');

		if($this->debug) echo 'PROCESSING POSTPROCESSORS<br>';

		foreach($this->processors['post'] as $postProc)
		{
			if(is_array($postProc) && $postProc[0] instanceof Dwoo_Processor)
				$compiled = call_user_func($postProc, $compiled);
			else
				$compiled = call_user_func($postProc, $this, $compiled);
		}
		unset($postProc);

		if($this->debug) echo 'COMPILATION COMPLETE : MEM USAGE : '.memory_get_usage().'<br>';

		$output = "<?php\n";
		// remove topLevelBlock
		array_shift($this->usedPlugins);
		// build plugin preloader
		foreach($this->usedPlugins as $plugin=>$type)
		{
			if($type & Dwoo::CUSTOM_PLUGIN) continue;
			switch($type)
			{
				case Dwoo::BLOCK_PLUGIN:
				case Dwoo::CLASS_PLUGIN:
					$output .= "if(class_exists('Dwoo_Plugin_$plugin', false)===false)\n\tDwoo_Loader::loadPlugin('$plugin');\n";
					break;
				case Dwoo::FUNC_PLUGIN:
					$output .= "if(function_exists('Dwoo_Plugin_$plugin')===false)\n\tDwoo_Loader::loadPlugin('$plugin');\n";
					break;
				case Dwoo::SMARTY_MODIFIER:
					$output .= "if(function_exists('smarty_modifier_$plugin')===false)\n\tDwoo_Loader::loadPlugin('$plugin');\n";
					break;
				case Dwoo::SMARTY_FUNCTION:
					$output .= "if(function_exists('smarty_function_$plugin')===false)\n\tDwoo_Loader::loadPlugin('$plugin');\n";
					break;
				case Dwoo::SMARTY_BLOCK:
					$output .= "if(function_exists('smarty_block_$plugin')===false)\n\tDwoo_Loader::loadPlugin('$plugin');\n";
					break;
				default:
					throw new Dwoo_Compilation_Exception('Type error for '.$plugin.' with type'.$type);
			}
		}

		$output .= $compiled."\n?>";

		$output = str_replace(self::PHP_CLOSE . self::PHP_OPEN, "\n", $output);

		if($this->debug) {
			echo '<hr><pre>';
			$lines = preg_split('{\r\n|\n}', htmlentities($output));
			foreach($lines as $i=>$line)
				echo ($i+1).'. '.$line."\r\n";
		}
		if($this->debug) echo '<hr></pre></pre>';

		if(!empty($this->errors))
			print_r($this->errors);

		$this->template = $this->dwoo = null;
		$tpl = null;

		return $output;
	}

	/**
	 * sets the scope
	 *
	 * set to null if the scope becomes "unstable" (i.e. too variable or unknown) so that
	 * variables are compiled in a more evaluative way than just $this->scope['key']
	 *
	 * @param mixed $scope a string i.e. "level1.level2" or an array i.e. array("level1", "level2")
	 * @param bool $absolute if true, the scope is set from the top level scope and not from the current scope
	 * @return array the current scope tree
	 */
	public function setScope($scope, $absolute = false)
	{
		$old = $this->scopeTree;

		if($scope===null)
		{
			unset($this->scope);
			$this->scope = null;
		}

		if(is_array($scope)===false)
			$scope = explode('.', $scope);

		if($absolute===true)
		{
			$this->scope =& $this->data;
			$this->scopeTree = array();
		}

		while(($bit = array_shift($scope)) !== null)
		{
			if($bit === '_parent' || $bit === '_')
			{
				array_pop($this->scopeTree);
				reset($this->scopeTree);
				$this->scope =& $this->data;
				$cnt = count($this->scopeTree);
				for($i=0;$i<$cnt;$i++)
					$this->scope =& $this->scope[$this->scopeTree[$i]];
			}
			elseif($bit === '_root' || $bit === '__')
			{
				$this->scope =& $this->data;
				$this->scopeTree = array();
			}
			elseif(isset($this->scope[$bit]))
			{
				$this->scope =& $this->scope[$bit];
				$this->scopeTree[] = $bit;
			}
			else
			{
				$this->scope[$bit] = array();
				$this->scope =& $this->scope[$bit];
				$this->scopeTree[] = $bit;
			}
		}

		return $old;
	}

	/**
	 * forces an absolute scope
	 *
	 * @deprecated
	 * @param mixed $scope a scope as a string or array
	 * @return array the current scope tree
	 */
	public function forceScope($scope)
	{
		return $this->setScope($scope, true);
	}

	/**
	 * adds a block to the top of the block stack
	 *
	 * @param string $type block type (name)
	 * @param array $params the parameters array
	 * @param int $paramtype the parameters type (see mapParams), 0, 1 or 2
	 * @return string the preProcessing() method's output
	 */
	public function addBlock($type, array $params, $paramtype)
	{
		$class = 'Dwoo_Plugin_'.$type;
		if(class_exists($class, false) === false)
			Dwoo_Loader::loadPlugin($type);

		$params = $this->mapParams($params, array($class, 'init'), $paramtype);

		$this->stack[] = array('type' => $type, 'params' => $params, 'custom' => false);
		$this->curBlock =& $this->stack[count($this->stack)-1];
		return call_user_func(array($class,'preProcessing'), $this, $params, '', '', $type);
	}

	/**
	 * adds a custom block to the top of the block stack
	 *
	 * @param string $type block type (name)
	 * @param array $params the parameters array
	 * @param int $paramtype the parameters type (see mapParams), 0, 1 or 2
	 * @return string the preProcessing() method's output
	 */
	public function addCustomBlock($type, array $params, $paramtype)
	{
		$callback = $this->customPlugins[$type]['callback'];
		if(is_array($callback))
			$class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
		else
			$class = $callback;

		$params = $this->mapParams($params, array($class, 'init'), $paramtype);

		$this->stack[] = array('type' => $type, 'params' => $params, 'custom' => true, 'class'=>$class);
		$this->curBlock =& $this->stack[count($this->stack)-1];
		return call_user_func(array($class,'preProcessing'), $this, $params, '', '', $type);
	}

	/**
	 * injects a block at the top of the plugin stack without calling its preProcessing method
	 *
	 * used by {else} blocks to re-add themselves after having closed everything up to their parent
	 *
	 * @param string $type block type (name)
	 * @param array $params parameters array
	 */
	public function injectBlock($type, array $params)
	{
		$class = 'Dwoo_Plugin_'.$type;
		if(class_exists($class, false) === false)
			Dwoo_Loader::loadPlugin($type);
		$this->stack[] = array('type' => $type, 'params' => $params, 'custom' => false);
		$this->curBlock =& $this->stack[count($this->stack)-1];
	}

	/**
	 * removes the closest-to-top block of the given type and all other
	 * blocks encountered while going down the block stack
	 *
	 * @param string $type block type (name)
	 * @return string the output of all postProcessing() method's return values of the closed blocks
	 */
	public function removeBlock($type)
	{
		$output = '';

		$pluginType = $this->getPluginType($type);
		if($pluginType & Dwoo::SMARTY_BLOCK)
			$type = 'smartyinterface';
		while(true)
		{
			while($top = array_pop($this->stack))
			{
				if($top['custom'])
					$class = $top['class'];
				else
					$class = 'Dwoo_Plugin_'.$top['type'];
				$output .= call_user_func(array($class, 'postProcessing'), $this, $top['params']);
				if($top['type'] === $type)
					break 2;
			}

			throw new Dwoo_Compilation_Exception('Syntax malformation, a block of type "'.$type.'" was closed but was not opened');
			break;
		}

		$this->curBlock =& $this->stack[count($this->stack)-1];

		return $output;
	}

	/**
	 * returns a reference to the first block of the given type encountered and
	 * optionally closes all blocks until it finds it
	 *
	 * this is mainly used by {else} plugins to close everything that was opened
	 * between their parent and themselves
	 *
	 * @param string $type the block type (name)
	 * @param bool $closeAlong whether to close all blocks encountered while going down the block stack or not
	 * @return &array the array is as such: array('type'=>pluginName, 'params'=>parameter array,
	 * 				  'custom'=>bool defining whether it's a custom plugin or not, for internal use)
	 */
	public function &findBlock($type, $closeAlong = false)
	{
		if($closeAlong===true)
		{
			while($b = end($this->stack))
			{
				if($b['type']===$type)
					return $this->stack[key($this->stack)];
				$this->removeTopBlock();
			}
		}
		else
		{
			end($this->stack);
			while($b = current($this->stack))
			{
				if($b['type']===$type)
					return $this->stack[key($this->stack)];
				prev($this->stack);
			}
		}

		throw new Dwoo_Compilation_Exception('A parent block of type "'.$type.'" is required and can not be found');
	}

	/**
	 * returns a reference to the current block array
	 *
	 * @return &array the array is as such: array('type'=>pluginName, 'params'=>parameter array,
	 * 				  'custom'=>bool defining whether it's a custom plugin or not, for internal use)
	 */
	public function &getCurrentBlock()
	{
		return $this->curBlock;
	}

	/**
	 * removes the block at the top of the stack and calls its postProcessing() method
	 *
	 * @return string the postProcessing() method's output
	 */
	public function removeTopBlock()
	{
		$o = array_pop($this->stack);
		if($o === null)
			throw new Dwoo_Compilation_Exception('Syntax malformation, a block of unknown type was closed but was not opened.');
		if($o['custom'])
			$class = $o['class'];
		else
			$class = 'Dwoo_Plugin_'.$o['type'];

		$this->curBlock =& $this->stack[count($this->stack)-1];

		return call_user_func(array($class, 'postProcessing'), $this, $o['params']);
	}

	/**
	 * returns the compiled parameters (for example a variable's compiled parameter will be "$this->scope['key']") out of the given parameter array
	 *
	 * @param array $params parameter array
	 * @return array filtered parameters
	 */
	public function getCompiledParams(array $params)
	{
		foreach($params as &$p)
			$p = $p[0];
		return $params;
	}

	/**
	 * returns the real parameters (for example a variable's real parameter will be its key, etc) out of the given parameter array
	 *
	 * @param array $params parameter array
	 * @return array filtered parameters
	 */
	public function getRealParams(array $params)
	{
		foreach($params as &$p)
			$p = $p[1];
		return $params;
	}

	/**
	 * entry point of the parser, it redirects calls to other parse* functions
	 *
	 * @param string $in the string within which we must parse something
	 * @param int $from the starting offset of the parsed area
	 * @param int $to the ending offset of the parsed area
	 * @param mixed $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by default
	 * @param string $curBlock the current parser-block being processed
	 * @param mixed $pointer a reference to a pointer that will be increased by the amount of characters parsed, or null by default
	 * @return string parsed values
	 */
	protected function parse($in, $from, $to, $parsingParams = false, $curBlock='', &$pointer = null)
	{
		if($to === null)
			$to = strlen($in);
		$first = $in[$from];
		$substr = substr($in, $from, $to-$from);

		if($this->debug) echo '<br />PARSE CALL : </pre>PARSING <b>'.htmlentities(substr($in, 0, $from)).'<u>'.htmlentities(substr($in, $from, $to-$from)).'</u>'.htmlentities(substr($in, $to)).'</b> @ '.$from.':'.$to.' in '.$curBlock.' : pointer='.$pointer.'<br/>';

		if($first==='$') // var
		{
			$out = $this->parseVar($in, $from, $to, $parsingParams, $curBlock, $pointer);
		}
		elseif($first==='%' && preg_match('#^%[a-z]#i', $substr)) // const
		{
			$out = $this->parseConst($in, $from, $to, $parsingParams, $curBlock, $pointer);
		}
		elseif($first==='"' || $first==="'") // string
		{
			$out = $this->parseString($in, $from, $to, $parsingParams, $curBlock, $pointer);
		}
		elseif(preg_match('#^[a-z][a-z0-9_]*('.(is_array($parsingParams)||$curBlock!='root'?'':' |').'\(|$)#i', $substr)) // func
		{
			$out = $this->parseFunction($in, $from, $to, $parsingParams, $curBlock, $pointer);
		}
		elseif(is_array($parsingParams) && preg_match('#^([a-z0-9_]+\s*=)(?:\s+|[^=]).*#i', $substr, $match)) // named parameter
		{
			if($this->debug) echo 'NAMED PARAM FOUND<br />';
			$len = strlen($match[1]);
			while(substr($in, $from+$len, 1)===' ')
				$len++;
			if($pointer !== null)
				$pointer += $len;

			$output = array(trim(substr(trim($match[1]),0,-1)), $this->parse($in, $from+$len, $to, false, 'namedparam', $pointer));

			$parsingParams[] = $output;
			return $parsingParams;
		}
		elseif($substr!=='' && (is_array($parsingParams) || $curBlock === 'namedparam' || $curBlock === 'condition')) // unquoted string, bool or number
		{
			$out = $this->parseOthers($in, $from, $to, $parsingParams, $curBlock, $pointer);
		}
		else // parse error
		{
			throw new Dwoo_Compilation_Exception('Parse error in <em>'.substr($in, 0, $from).'<u>'.substr($in, $from, $to-$from).'</u>'.substr($in, $to).'</em> @ '.$from);
		}

		if(empty($out))
			return '';
		if($curBlock === 'root' && substr($out, 0, strlen(self::PHP_OPEN)) !== self::PHP_OPEN)
			return self::PHP_OPEN .'echo '.$out.';'. self::PHP_CLOSE;
		else
			return $out;
	}

	/**
	 * parses a function call
	 *
	 * @param string $in the string within which we must parse something
	 * @param int $from the starting offset of the parsed area
	 * @param int $to the ending offset of the parsed area
	 * @param mixed $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by default
	 * @param string $curBlock the current parser-block being processed
	 * @param mixed $pointer a reference to a pointer that will be increased by the amount of characters parsed, or null by default
	 * @return string parsed values
	 */
	protected function parseFunction($in, $from, $to, $parsingParams = false, $curBlock='', &$pointer = null)
	{
		$cmdstr = substr($in, $from, $to-$from);

		if($this->debug) echo 'FUNC FOUND<br />';

		$paramsep = '';
		if(is_array($parsingParams) || $curBlock != 'root')
			$ppos1 = false;
		else
			$ppos1 = strpos($cmdstr, ' ');
		$ppos2 = strpos($cmdstr, '(');

		if($ppos1 !== false && $ppos2 !== false)
		{
			$paramspos = min($ppos1, $ppos2);
			if($paramspos === $ppos2)
				$paramsep = ')';
		}
		elseif($ppos1 !== false)
			$paramspos = $ppos1;
		else
		{
			$paramspos = $ppos2;
			$paramsep = ')';
		}

		$state = 0;

		if($paramspos === false)
		{
			if(strpos($cmdstr,' '))
				$func = substr($cmdstr, 0, strpos($cmdstr,' '));
			else
				$func = $cmdstr;
			$params = array();

			if($curBlock !== 'root')
			{
				return $this->parseOthers($in, $from, $to, $parsingParams, $curBlock, $pointer);
			}
		}
		else
		{
			$func = substr($cmdstr, 0, $paramspos);
			$paramstr = substr($cmdstr, $paramspos+1);
			if(substr($paramstr, -1, 1) === $paramsep)
				$paramstr = substr($paramstr, 0, -1);

			if(strlen($paramstr)===0)
			{
				$params = array();
				$paramstr = '';
			}
			else
			{
				$ptr = 0;
				$params = array();
				while($ptr < strlen($paramstr))
				{
					while(true)
					{
						if($ptr >= strlen($paramstr))
							break 2;

						if($func !== 'if' && $func !== 'elseif' && $paramstr[$ptr] === ')')
						{
							if($this->debug) echo 'PARAM PARSING ENDED, ")" FOUND, POINTER AT '.$ptr.'<br/>';
							break 2;
						}

						if(($paramstr[$ptr] === ' ' || $paramstr[$ptr] === ','))
							$ptr++;
						else
							break;
					}

					if($this->debug) echo 'FUNC START PARAM PARSING WITH POINTER AT '.$ptr.'<br/>';

					if($func === 'if' || $func === 'elseif')
						$params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'condition', $ptr);
					else
						$params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'function', $ptr);

					if($this->debug) echo 'PARAM PARSED, POINTER AT '.$ptr.'<br/>';
				}
				$paramstr = substr($paramstr, 0, $ptr);
				$state = 0;
				foreach($params as $k=>$p)
				{
					if(is_array($p) && is_array($p[1]))
						$state |= 2;
					else
					{
						if($state === 2 && preg_match('#^(["\'])(.+?)\1$#', $p[0], $m))
							$params[$k] = array($m[2], array('true', 'true'));
						else
							$state |= 1;
					}
				}
				if($state === 3)
					throw new Dwoo_Compilation_Exception('Function calls can not have both named and un-named parameters');
			}
		}

		if($pointer !== null)
		{
			$pointer += (isset($paramstr) ? strlen($paramstr) : 0) + (')' === $paramsep ? 2 : 0) + strlen($func);
			if($this->debug) echo 'FUNC ADDS '.((isset($paramstr) ? strlen($paramstr) : 0) + (')' === $paramsep ? 2 : 0) + strlen($func)).' TO POINTER<br/>';
		}

		if($curBlock === 'method' || $func === 'do')
		{
			$pluginType = Dwoo::NATIVE_PLUGIN;
		}
		else
		{
			$pluginType = $this->getPluginType($func);

			// add block
			if($pluginType & Dwoo::BLOCK_PLUGIN)
			{
				if($curBlock !== 'root' || is_array($parsingParams))
					throw new Dwoo_Compilation_Exception('Block plugins can not be used as other plugin\'s arguments');
				if($pluginType & Dwoo::CUSTOM_PLUGIN)
					return $this->addCustomBlock($func, $params, $state);
				else
					return $this->addBlock($func, $params, $state);
			}
			elseif($pluginType & Dwoo::SMARTY_BLOCK)
			{
				if($curBlock !== 'root' || is_array($parsingParams))
					throw new Dwoo_Compilation_Exception('Block plugins can not be used as other plugin\'s arguments');

				if($state===2)
				{
					array_unshift($params, array('__functype', array($pluginType, $pluginType)));
					array_unshift($params, array('__funcname', array($func, $func)));
				}
				else
				{
					array_unshift($params, array($pluginType, $pluginType));
					array_unshift($params, array($func, $func));
				}

				return $this->addBlock('smartyinterface', $params, $state);
			}
		}

		if($pluginType & Dwoo::NATIVE_PLUGIN || $pluginType & Dwoo::SMARTY_FUNCTION || $pluginType & Dwoo::SMARTY_BLOCK)
		{
			$params = $this->mapParams($params, null, $state);
		}
		elseif($pluginType & Dwoo::CLASS_PLUGIN)
		{
			if($pluginType & Dwoo::CUSTOM_PLUGIN)
				$params = $this->mapParams($params, array($this->customPlugins[$func]['class'], $this->customPlugins[$func]['function']), $state);
			else
				$params = $this->mapParams($params, array('Dwoo_Plugin_'.$func, ($pluginType & Dwoo::COMPILABLE_PLUGIN) ? 'compile' : 'process'), $state);
		}
		elseif($pluginType & Dwoo::FUNC_PLUGIN)
		{
			if($pluginType & Dwoo::CUSTOM_PLUGIN)
				$params = $this->mapParams($params, $this->customPlugins[$func]['callback'], $state);
			else
				$params = $this->mapParams($params, 'Dwoo_Plugin_'.$func.(($pluginType & Dwoo::COMPILABLE_PLUGIN) ? '_compile' : ''), $state);
		}
		elseif($pluginType & Dwoo::SMARTY_MODIFIER)
		{
			$output = 'smarty_modifier_'.$func.'('.implode(', ', $params).')';
		}

		// keep php-syntax-safe values for non-block plugins
		foreach($params as &$p)
			$p = $p[0];
		if($pluginType & Dwoo::NATIVE_PLUGIN)
		{
			if($func === 'do')
			{
				if(isset($params['*']))
					$output = implode(';', $params['*']).';';
				else
					$output = '';

				if(is_array($parsingParams) || $curBlock !== 'root')
					throw new Dwoo_Compilation_Exception('Do can not be used inside another function or block');
				else
					return self::PHP_OPEN.$output.self::PHP_CLOSE;
			}
			else
			{
				if(isset($params['*']))
					$output = $func.'('.implode(', ', $params['*']).')';
				else
					$output = $func.'()';
			}
		}
		elseif($pluginType & Dwoo::FUNC_PLUGIN)
		{
			if($pluginType & Dwoo::COMPILABLE_PLUGIN)
			{
				$funcCompiler = 'Dwoo_Plugin_'.$func.'_compile';
				array_unshift($params, $this);
				$output = call_user_func_array($funcCompiler, $params);
			}
			else
			{
				array_unshift($params, '$this');
				$params = $this->implode_r($params);

				if($pluginType & Dwoo::CUSTOM_PLUGIN)
				{
					$callback = $this->customPlugins[$func]['callback'];
					$output = 'call_user_func(\''.$callback.'\', '.$params.')';
				}
				else
					$output = 'Dwoo_Plugin_'.$func.'('.$params.')';
			}
		}
		elseif($pluginType & Dwoo::CLASS_PLUGIN)
		{
			if($pluginType & Dwoo::COMPILABLE_PLUGIN)
			{
				$funcCompiler = array('Dwoo_Plugin_'.$func, 'compile');
				array_unshift($params, $this);
				$output = call_user_func_array($funcCompiler, $params);
			}
			else
			{
				$params = $this->implode_r($params);
				if($pluginType & Dwoo::CUSTOM_PLUGIN)
				{
					$callback = $this->customPlugins[$func]['callback'];
					if(!is_array($callback))
					{
						if(($ref = new ReflectionMethod($callback, 'process')) && $ref->isStatic())
							$output = 'call_user_func(array(\''.$callback.'\', \'process\'), '.$params.')';
						else
							$output = 'call_user_func(array($this->getObjectPlugin(\''.$callback.'\'), \'process\'), '.$params.')';
					}
					elseif(is_object($callback[0]))
						$output = 'call_user_func(array($this->plugins[\''.$func.'\'][\'callback\'][0], \''.$callback[1].'\'), '.$params.')';
					elseif(($ref = new ReflectionMethod($callback[0], $callback[1])) && $ref->isStatic())
						$output = 'call_user_func(array(\''.$callback[0].'\', \''.$callback[1].'\'), '.$params.')';
					else
						$output = 'call_user_func(array($this->getObjectPlugin(\''.$callback[0].'\'), \''.$callback[1].'\'), '.$params.')';
				}
				else
					$output = '$this->classCall(\''.$func.'\', array('.$params.'))';
			}
		}
		elseif($pluginType & Dwoo::SMARTY_FUNCTION)
		{
			$params = $this->implode_r($params['*'], true);

			if($pluginType & Dwoo::CUSTOM_PLUGIN)
			{
				$callback = $this->customPlugins[$func]['callback'];
				if(is_array($callback))
				{
					if(is_object($callback[0]))
						$output = 'call_user_func_array(array($this->plugins[\''.$func.'\'][\'callback\'][0], \''.$callback[1].'\'), array(array('.$params.'), $this))';
					else
						$output = 'call_user_func_array(array(\''.$callback[0].'\', \''.$callback[1].'\'), array(array('.$params.'), $this))';
				}
				else
					$output = $callback.'(array('.$params.'), $this)';
			}
			else
				$output = 'smarty_function_'.$func.'(array('.$params.'), $this)';
		}

		if(is_array($parsingParams))
		{
			$parsingParams[] = array($output, $output);
			return $parsingParams;
		}
		elseif($curBlock === 'namedparam')
			return array($output, $output);
		else
			return $output;
	}

	/**
	 * parses a string
	 *
	 * @param string $in the string within which we must parse something
	 * @param int $from the starting offset of the parsed area
	 * @param int $to the ending offset of the parsed area
	 * @param mixed $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by default
	 * @param string $curBlock the current parser-block being processed
	 * @param mixed $pointer a reference to a pointer that will be increased by the amount of characters parsed, or null by default
	 * @return string parsed values
	 */
	protected function parseString($in, $from, $to, $parsingParams = false, $curBlock='', &$pointer = null)
	{
		$substr = substr($in, $from, $to-$from);
		$first = $substr[0];

		if($this->debug) echo 'STRING FOUND<br />';
		$strend = false;
		$o = $from+1;
		while($strend===false)
		{
			$strend = strpos($in, $first, $o);
			if($strend === false)
			{
				throw new Dwoo_Compilation_Exception('Unfinished string in : <strong>'.substr($in, 0, $from).'<u>'.substr($in, $from, $to-$from).'</u>'.substr($in, $to).'</strong>');
			}
			if(substr($in, $strend-1, 1) === '\\')
			{
				$o = $strend+1;
				$strend = false;
			}
		}
		if($curBlock !== 'modifier' && substr($in, $strend+1, 1)==='|')
		{
			if($strend !== false)
				$realend = $strend-$from+1;
			$strend = strpos($in, ' ', $strend+1);
			if($strend===false)
				$strend = strlen($in)-1;
		}
		$srcOutput = substr($in, $from, $strend+1-$from);

		if($pointer !== null)
			$pointer += strlen($srcOutput);

		if(isset($realend))
			$output = $this->replaceStringVars(substr($srcOutput, 0, $realend), $first) . substr($srcOutput, $realend);
		else
			$output = $this->replaceStringVars($srcOutput, $first);

		// handle modifiers
		if($curBlock !== 'modifier' && preg_match('#(.+?)((?:\|(?:@?[a-z0-9_]+(?::[^\s]*)*))+)#i', $output, $match))
		{
			$modstr = $match[2];
			$output = $match[1];
			$strend += strlen($match[1]);

			if($curBlock === 'root' && substr($modstr, -1) === '}')
				$modstr = substr($modstr, 0, -1);
			$output = $this->replaceModifiers(array(null, null, $output, $modstr), 'string');
		}

		if($curBlock !== 'namedparam' && $curBlock !== 'modifier' && $curBlock !== 'function' && $curBlock !== 'condition' && strlen(substr($in, 0, $to)) > $strend+1)
			$output .= $this->parse($in, $strend+1, $to, $parsingParams);

		if(is_array($parsingParams))
		{
			$parsingParams[] = array($output, substr($srcOutput,1,-1));
			return $parsingParams;
		}
		elseif($curBlock === 'namedparam')
			return array($output, substr($srcOutput,1,-1));
		else
			return $output;
	}

	/**
	 * parses a constant
	 *
	 * @param string $in the string within which we must parse something
	 * @param int $from the starting offset of the parsed area
	 * @param int $to the ending offset of the parsed area
	 * @param mixed $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by default
	 * @param string $curBlock the current parser-block being processed
	 * @param mixed $pointer a reference to a pointer that will be increased by the amount of characters parsed, or null by default
	 * @return string parsed values
	 */
	protected function parseConst($in, $from, $to, $parsingParams = false, $curBlock='', &$pointer = null)
	{
		$substr = substr($in, $from, $to-$from);

		if($this->debug)
			echo 'CONST FOUND : '.$substr.'<br />';

		if(!preg_match('#^%([a-z0-9_:]+)#i', $substr, $m))
			throw new Dwoo_Compilation_Exception('Invalid constant');

		$output = $this->parseConstKey($m[1], $curBlock);

		if(is_array($parsingParams))
		{
			$parsingParams[] = array($output, $m[1]);
			return $parsingParams;
		}
		elseif($curBlock === 'namedparam')
			return array($output, $m[1]);
		else
			return $output;
	}

	/**
	 * parses a constant
	 *
	 * @param string $key the constant to parse
	 * @param string $curBlock the current parser-block being processed
	 * @return string parsed constant
	 */
	protected function parseConstKey($key, $curBlock)
	{
		if($this->securityPolicy !== null && $this->securityPolicy->getConstantHandling() === Dwoo_Security_Policy::CONST_DISALLOW)
			return 'null';

		if($curBlock !== 'root')
			$output = '(defined("'.$key.'") ? '.$key.' : null)';
		else
			$output = $key;

		return $output;
	}

	/**
	 * parses a variable
	 *
	 * @param string $in the string within which we must parse something
	 * @param int $from the starting offset of the parsed area
	 * @param int $to the ending offset of the parsed area
	 * @param mixed $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by default
	 * @param string $curBlock the current parser-block being processed
	 * @param mixed $pointer a reference to a pointer that will be increased by the amount of characters parsed, or null by default
	 * @return string parsed values
	 */
	protected function parseVar($in, $from, $to, $parsingParams = false, $curBlock='', &$pointer = null)
	{
		$substr = substr($in, $from, $to-$from);

		if(preg_match('#(\$?\.?[a-z0-9_:]*(?:(?:(?:\.|->)(?:[a-z0-9_:]+|(?R))|\[(?:[a-z0-9_:]+|(?R))\]))*)' . // var key
			($curBlock==='root' || $curBlock==='function' || $curBlock==='condition' || $curBlock==='variable' || $curBlock==='expression' ? '(\([^)]*?\)(?:->[a-z0-9_]+(?:\([^)]*?\))?)*)?' : '()') . // method call
			($curBlock==='root' || $curBlock==='function' || $curBlock==='condition' || $curBlock==='variable' || $curBlock==='string' ? '((?:(?:[+/*%=-])(?:(?<!=)=?-?[$%][a-z0-9.[\]>_:-]+(?:\([^)]*\))?|(?<!=)=?-?[0-9.,]*|[+-]))*)':'()') . // simple math expressions
			($curBlock!=='modifier'? '((?:\|(?:@?[a-z0-9_]+(?:(?::("|\').+?\5|:[^\s`"\']*))*))+)?':'(())') . // modifiers
			'#i', $substr, $match))
		{
			$key = substr($match[1],1);

			$matchedLength = strlen($match[0]);
			$hasModifiers = isset($match[4]) && !empty($match[4]);
			$hasExpression = isset($match[3]) && !empty($match[3]);
			$hasMethodCall = isset($match[2]) && !empty($match[2]);

			if($hasMethodCall)
			{
				$key = substr($match[1], 1, strrpos($match[1], '->')-1);
				$methodCall = substr($match[1], strrpos($match[1], '->')) . $match[2];
			}

			if($pointer !== null)
				$pointer += $matchedLength;

			// replace useless brackets by dot accessed vars
			$key = preg_replace('#\[([^\[.->]+)\]#', '.$1', $key);

			// prevent $foo->$bar calls because it doesn't seem worth the trouble
			if(strpos($key, '->$') !== false)
				throw new Dwoo_Compilation_Exception('You can not access an object\'s property using a variable name.');

			if($this->debug)
			{
				if($hasMethodCall)
					echo 'METHOD CALL FOUND : $'.$key.$methodCall.'<br />';
				else
					echo 'VAR FOUND : $'.$key.'<br />';
			}

			$key = str_replace('"','\\"',$key);

			$cnt=substr_count($key, '$');
			if($cnt > 0)
			{
				$uid = 0;
				$parsed = array($uid => '');
				$current =& $parsed;
				$curTxt =& $parsed[$uid++];
				$tree = array();
				$chars = str_split($key, 1);
				while(($char = array_shift($chars)) !== null)
				{
					if($char === '[')
					{
						$tree[] =& $current;
						$current[$uid] = array($uid+1 => '');
						$current =& $current[$uid++];
						$curTxt =& $current[$uid++];
					}
					elseif($char === ']')
					{
						$current =& $tree[count($tree)-1];
						array_pop($tree);
						if(current($chars) !== '[' && current($chars) !== false && current($chars) !== ']')
						{
							$current[$uid] = '';
							$curTxt =& $current[$uid++];
						}
					}
					else
					{
						$curTxt .= $char;
					}
				}
				unset($uid, $current, $curTxt, $tree, $chars);

				if($this->debug) echo 'RECURSIVE VAR REPLACEMENT : '.$key.'<br>';

				$key = $this->flattenVarTree($parsed);

				if($this->debug) echo 'RECURSIVE VAR REPLACEMENT DONE : '.$key.'<br>';

				$output = preg_replace('#(^""\.|""\.|\.""$|(\()""\.|\.""(\)))#', '$2$3', '$this->readVar("'.$key.'")');
			}
			else
			{
				$output = $this->parseVarKey($key, $curBlock);
			}

			// methods
			if($hasMethodCall)
			{
				preg_match_all('{->([a-z0-9_]+)(\([^)]*\))?}i', $methodCall, $calls);
				foreach($calls[1] as $i=>$method)
				{
					$args = $calls[2][$i];
					// property
					if($args === '')
						$output = '(property_exists($tmp = '.$output.', \''.$method.'\') ? $tmp->'.$method.' : null)';
					// method
					else
					{
						if($args === '()')
							$parsedCall = '->'.$method.$args;
						else
							$parsedCall = '->'.$this->parseFunction($method.$args, 0, strlen($method.$args), false, 'method');
						$output = '(is_object($tmp = '.$output.') ? (method_exists($tmp, \''.$method.'\') ? $tmp'.$parsedCall.' : $this->triggerError(\'Call to an undefined method : <em>\'.get_class($tmp).\'::'.$method.'()</em>\')) : $this->triggerError(\'Method <em>'.$method.'()</em> was called on a non-object (\'.var_export($tmp, true).\')\'))';
					}
				}
			}

			// expressions
			if($hasExpression)
			{
				preg_match_all('#(?:([+/*%=-])(=?-?[%$][a-z0-9.[\]>_:-]+(?:\([^)]*\))?|=?-?[0-9.,]+|\1))#i', $match[3], $expMatch);

				foreach($expMatch[1] as $k=>$operator)
				{
					if(substr($expMatch[2][$k],0,1)==='=')
					{
						$assign = true;
						if($operator === '=')
							throw new Dwoo_Compilation_Exception('Invalid expression, <em>'.$substr.'</em>, can not use "==" in expressions');
						if($curBlock !== 'root')
							throw new Dwoo_Compilation_Exception('Invalid expression, <em>'.$substr.'</em>, "=" can only be used in pure expressions like {$foo+=3}, {$foo="bar"}');
						$operator .= '=';
						$expMatch[2][$k] = substr($expMatch[2][$k], 1);
					}

					if(substr($expMatch[2][$k],0,1)==='-' && strlen($expMatch[2][$k]) > 1)
					{
						$operator .= '-';
						$expMatch[2][$k] = substr($expMatch[2][$k], 1);
					}
					if(($operator==='+'||$operator==='-') && $expMatch[2][$k]===$operator)
					{
						$output = '('.$output.$operator.$operator.')';
						break;
					}
					elseif(substr($expMatch[2][$k], 0, 1) === '$')
					{
						$output = '('.$output.' '.$operator.' '.$this->parseVar($expMatch[2][$k], 0, strlen($expMatch[2][$k]), false, 'expression').')';
					}
					elseif(substr($expMatch[2][$k], 0, 1) === '%')
					{
						$output = '('.$output.' '.$operator.' '.$this->parseConst($expMatch[2][$k], 0, strlen($expMatch[2][$k]), false, 'expression').')';
					}
					elseif(!empty($expMatch[2][$k]))
						$output = '('.$output.' '.$operator.' '.str_replace(',', '.', $expMatch[2][$k]).')';
					else
						throw new Dwoo_Compilation_Exception('Unfinished expression, <em>'.$substr.'</em>, missing var or number after math operator');
				}
			}
			// var assignment
			elseif($curBlock === 'root' && substr(trim(substr($substr, $matchedLength)), 0, 1) === '=')
			{
				$value = trim(substr(trim(substr($substr, $matchedLength)), 1));

				$parts = array();
				$parts = $this->parse($value, 0, strlen($value), $parts, 'condition');

				// load if plugin
				try {
					$this->getPluginType('if');
				} catch (Dwoo_Exception $e) {
					throw new Dwoo_Compilation_Exception('Assignments require the "if" plugin to be accessible');
				}

				$parts = $this->mapParams($parts, array('Dwoo_Plugin_if', 'init'), 1);
				$value = Dwoo_Plugin_if::replaceKeywords($parts, $this);

				$output .= '='.implode(' ',$value);
				$assign = true;
			}

			// handle modifiers
			if($curBlock !== 'modifier' && $hasModifiers)
			{
				$output = $this->replaceModifiers(array(null, null, $output, $match[4]), 'var');
			}

			if(is_array($parsingParams))
			{
				$parsingParams[] = array($output, $key);
				return $parsingParams;
			}
			elseif($curBlock === 'namedparam')
				return array($output, $key);
			elseif($curBlock === 'string')
				return array($matchedLength, $output);
			elseif($curBlock === 'expression' || $curBlock === 'variable')
				return $output;
			elseif(isset($assign))
				return self::PHP_OPEN.$output.';'.self::PHP_CLOSE;
			else
				return $output;
		}
		else
		{
			if($curBlock === 'string')
				return array(0, '');
			else
			{
				throw new Dwoo_Compilation_Exception('Invalid variable name <em>'.$substr.'</em>');
			}
		}
	}

	/**
	 * parses a constant variable (a variable that doesn't contain another variable) and preprocesses it to save runtime processing time
	 *
	 * @param string $key the variable to parse
	 * @param string $curBlock the current parser-block being processed
	 * @return string parsed variable
	 */
	protected function parseVarKey($key, $curBlock)
	{
		if($key === '')
		{
			return '$this->scope';
		}
		if(substr($key, 0, 1) === '.')
			$key = 'dwoo'.$key;
		if(preg_match('#dwoo\.(get|post|server|cookies|session|env|request)((?:\.[a-z0-9_-]+)+)#i', $key, $m))
		{
			$global = strtoupper($m[1]);
			if($global === 'COOKIES')
				$global = 'COOKIE';
			$key = '$_'.$global;
			foreach(explode('.', ltrim($m[2], '.')) as $part)
				$key .= '['.var_export($part, true).']';
			if($curBlock === 'root')
				$output = $key;
			else
				$output = '(isset('.$key.')?'.$key.':null)';
		}
		elseif(preg_match('#dwoo\.const\.([a-z0-9_:]+)#i', $key, $m))
		{
			return $this->parseConstKey($m[1], $curBlock);
		}
		elseif($this->scope !== null)
		{
			if(strstr($key, '.') === false && strstr($key, '[') === false && strstr($key, '->') === false)
			{
				if($key === 'dwoo')
				{
					$output = '$this->globals';
				}
				elseif($key === '_root' || $key === '__')
				{
					$output = '$this->data';
				}
				elseif($key === '_parent' || $key === '_')
				{
					$output = '$this->readParentVar(1)';
				}
				elseif($key === '_key')
				{
					$output = '$tmp_key';
				}
				else
				{
					if($curBlock === 'root')
						$output = '$this->scope["'.$key.'"]';
					else
						$output = '(isset($this->scope["'.$key.'"]) ? $this->scope["'.$key.'"] : null)';
				}
			}
			else
			{
				preg_match_all('#(\[|->|\.)?([a-z0-9_]+)\]?#i', $key, $m);

				$i = $m[2][0];
				if($i === '_parent' || $i === '_')
				{
					$parentCnt = 0;

					while(true)
					{
						$parentCnt++;
						array_shift($m[2]);
						array_shift($m[1]);
						if(current($m[2]) === '_parent')
							continue;
						break;
					}

					$output = '$this->readParentVar('.$parentCnt.')';
				}
				else
				{
					if($i === 'dwoo')
					{
						$output = '$this->globals';
						array_shift($m[2]);
						array_shift($m[1]);
					}
					elseif($i === '_root' || $i === '__')
					{
						$output = '$this->data';
						array_shift($m[2]);
						array_shift($m[1]);
					}
					elseif($i === '_key')
					{
						$output = '$tmp_key';
					}
					else
					{
						$output = '$this->scope';
					}

					while(count($m[1]) && $m[1][0] !== '->')
					{
						$output .= '["'.$m[2][0].'"]';
						array_shift($m[2]);
						array_shift($m[1]);
					}

					if($curBlock !== 'root')
						$output = '(isset('.$output.') ? '.$output.':null)';
				}

				if(count($m[2]))
				{
					unset($m[0]);
					$output = '$this->readVarInto('.str_replace("\n", '', var_export($m, true)).', '.$output.')';
				}
			}
		}
		else
		{
			preg_match_all('#(\[|->|\.)?([a-z0-9_]+)\]?#i', $key, $m);
			unset($m[0]);
			$output = '$this->readVar('.str_replace("\n", '', var_export($m, true)).')';
		}

		return $output;
	}

	/**
	 * flattens a variable tree, this helps in parsing very complex variables such as $var.foo[$foo.bar->baz].baz,
	 * it computes the contents of the brackets first and works out from there
	 *
	 * @param array $tree the variable tree parsed by he parseVar() method that must be flattened
	 * @param bool $recursed leave that to false by default, it is only for internal use
	 * @return string flattened tree
	 */
	protected function flattenVarTree(array $tree, $recursed=false)
	{
		$out = $recursed ?  '".$this->readVarInto(' : '';
		foreach($tree as $bit)
		{
			if(is_array($bit))
				$out.='.'.$this->flattenVarTree($bit, false);
			else
			{
				$key = str_replace('"','\\"',$bit);
				$cnt = substr_count($key, '$');

				if($this->debug) echo 'PARSING SUBVARS IN : '.$key.'<br>';
				if($cnt > 0)
				{
					while(--$cnt >= 0)
					{
						if(isset($last))
						{
							$last = strrpos($key, '$', - (strlen($key) - $last + 1));
						}
						else
						{
							$last = strrpos($key, '$');
						}
						preg_match('#\$[a-z0-9_]+((?:(?:\.|->)(?:[a-z0-9_]+|(?R))|\[(?:[a-z0-9_]+|(?R))\]))*'.
								  '((?:(?:[+/*%-])(?:\$[a-z0-9.[\]>_:-]+(?:\([^)]*\))?|[0-9.,]*))*)#i', substr($key, $last), $submatch);

						$len = strlen($submatch[0]);
						$key = substr_replace(
							$key,
							preg_replace_callback(
								'#(\$[a-z0-9_]+((?:(?:\.|->)(?:[a-z0-9_]+|(?R))|\[(?:[a-z0-9_]+|(?R))\]))*)'.
								'((?:(?:[+/*%-])(?:\$[a-z0-9.[\]>_:-]+(?:\([^)]*\))?|[0-9.,]*))*)#i',
								array($this, 'replaceVarKeyHelper'), substr($key, $last, $len)
							),
							$last,
							$len
						);
						if($this->debug) echo 'RECURSIVE VAR REPLACEMENT DONE : '.$key.'<br>';
					}

					$out .= $key;
				}
				else
				{
					$out .= $key;
				}
			}
		}
		$out .= $recursed ? ')."' : '';
		return $out;
	}

	/**
	 * helper function that parses a variable
	 *
	 * @param array $match the matched variable, array(1=>"string match")
	 * @return string parsed variable
	 */
	protected function replaceVarKeyHelper($match)
	{
		return '".'.$this->parseVar($match[0], 0, strlen($match[0]), false, 'variable').'."';
	}

	/**
	 * parses various constants, operators or non-quoted strings
	 *
	 * @param string $in the string within which we must parse something
	 * @param int $from the starting offset of the parsed area
	 * @param int $to the ending offset of the parsed area
	 * @param mixed $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by default
	 * @param string $curBlock the current parser-block being processed
	 * @param mixed $pointer a reference to a pointer that will be increased by the amount of characters parsed, or null by default
	 * @return string parsed values
	 */
	protected function parseOthers($in, $from, $to, $parsingParams = false, $curBlock='', &$pointer = null)
	{
		$first = $in[$from];
		$substr = substr($in, $from, $to-$from);

		$end = strlen($substr);

		if($curBlock === 'condition')
		{
			$breakChars = array('(', ')', ' ', '||', '&&', '|', '&', '>=', '<=', '===', '==', '=', '!==', '!=', '<<', '<', '>>', '>', '^', '~', ',', '+', '-', '*', '/', '%', '!');
		}
		elseif($curBlock === 'modifier')
			$breakChars = array(' ', ',', ')', ':', '|');
		else
			$breakChars = array(' ', ',', ')');

		$breaker = false;
		while(list($k,$char) = each($breakChars))
		{
			$test = strpos($substr,$char);
			if($test !== false && $test < $end)
			{
				$end = $test;
				$breaker = $k;
			}
		}

		if($curBlock === 'condition')
		{
			if($end === 0 && $breaker !== false)
			{
				$end = strlen($breakChars[$breaker]);
			}
		}

		if($end !== false)
			$substr = substr($substr, 0, $end);

		if($pointer !== null)
			$pointer += strlen($substr);

		$src = $substr;

		if(strtolower($substr) === 'false' || strtolower($substr) === 'no' || strtolower($substr) === 'off')
		{
			if($this->debug) echo 'BOOLEAN(FALSE) PARSED<br />';
			$substr = 'false';
		}
		elseif(strtolower($substr) === 'true' || strtolower($substr) === 'yes' || strtolower($substr) === 'on')
		{
			if($this->debug) echo 'BOOLEAN(TRUE) PARSED<br />';
			$substr = 'true';
		}
		elseif($substr === 'null' || $substr === 'NULL')
		{
			if($this->debug) echo 'NULL PARSED<br />';
			$substr = 'null';
		}
		elseif(is_numeric($substr))
		{
			if($this->debug) echo 'NUMBER PARSED<br />';
			$substr = (float) $substr;
		}
		elseif(preg_match('{^-?(\d+|\d*(\.\d+))\s*([/*%+-]\s*-?(\d+|\d*(\.\d+)))+$}', $substr))
		{
			if($this->debug) echo 'SIMPLE MATH PARSED<br />';
			$substr = '('.$substr.')';
		}
		elseif($curBlock === 'condition' && array_search($substr, $breakChars, true) !== false)
		{
			if($this->debug) echo 'BREAKCHAR PARSED<br />';
			$substr = '"'.$substr.'"';
		}
		else
		{
			if($this->debug) echo 'BLABBER CASTED AS STRING<br />';

			$substr = $this->replaceStringVars('"'.str_replace('"','\\"',$substr).'"', '"', $curBlock);
		}

		if(is_array($parsingParams))
		{
			$parsingParams[] = array($substr, $src);
			return $parsingParams;
		}
		elseif($curBlock === 'namedparam')
			return array($substr, $src);
		else
			throw new Exception('Something went wrong');
	}

	/**
	 * replaces variables within a parsed string
	 *
	 * @param string $string the parsed string
	 * @param string $first the first character parsed in the string, which is the string delimiter (' or ")
	 * @param string $curBlock the current parser-block being processed
	 * @return string the original string with variables replaced
	 */
	protected function replaceStringVars($string, $first, $curBlock='')
	{
		// replace vars
		$cnt=substr_count($string, '$');
		if($this->debug) echo 'STRING VAR REPLACEMENT : '.$string.'<br>';
		while(--$cnt >= 0)
		{
			if(isset($last))
			{
				$last = strrpos($string, '$', - (strlen($string) - $last + 1));
			}
			else
			{
				$last = strrpos($string, '$');
			}

			if(array_search($string[$last-1], array('\\', '/', '*', '+', '-', '%')) !== false)
				continue;

			$var = $this->parse($string, $last, null, false, $curBlock === 'modifier' ? 'modifier' : 'string');
			$len = $var[0];
			$string = substr_replace($string, $first.'.'.$var[1].'.'.$first, $last, $len);
			if($this->debug) echo 'STRING VAR REPLACEMENT DONE : '.$string.'<br>';
		}

		// handle modifiers
		$string = preg_replace_callback('#("|\')\.(.+?)\.\1((?:\|(?:@?[a-z0-9_]+(?:(?::("|\').+?\4|:[^\s`"\']*))*))+)#i', array($this, 'replaceModifiers'), $string);

		// replace escaped dollar operators by unescaped ones if required
		if($first==="'")
			$string = str_replace('\\$', '$', $string);

		// remove backticks around strings if needed
		$string = preg_replace('#`(("|\').+?\2)`#','$1',$string);

		return $string;
	}

	/**
	 * replaces the modifiers applied to a string or a variable
	 *
	 * @param array $m the regex matches that must be array(1=>"double or single quotes enclosing a string, when applicable", 2=>"the string or var", 3=>"the modifiers matched")
	 * @param string $curBlock the current parser-block being processed
	 * @return string the input enclosed with various function calls according to the modifiers found
	 */
	protected function replaceModifiers(array $m, $curBlock)
	{
		if($this->debug) echo 'PARSING MODIFIERS : '.$m[3].'<br />';

		// remove first pipe
		$cmdstrsrc = substr($m[3],1);
		// remove last quote if present
		if(substr($cmdstrsrc,-1,1) === $m[1])
		{
			$cmdstrsrc = substr($cmdstrsrc, 0, -1);
			$add = $m[1];
		}

		$output = $m[2];

		while(strlen($cmdstrsrc) > 0)
		{
			$cmdstr = $cmdstrsrc;
			$paramsep = ':';
			$paramspos = strpos($cmdstr, $paramsep);
			$funcsep = strpos($cmdstr, '|');
			if($funcsep !== false && ($paramspos === false || $paramspos > $funcsep))
			{
				$paramspos = false;
				$cmdstr = substr($cmdstr, 0, $funcsep);
			}

			$state = 0;
			if($paramspos === false)
			{
				$func = $cmdstr;
				$cmdstrsrc = substr($cmdstrsrc, strlen($func)+1);
				$params = array();
			}
			else
			{
				$func = substr($cmdstr, 0, $paramspos);
				$paramstr = substr($cmdstr, $paramspos+1);
				if(substr($paramstr, -1, 1) === $paramsep)
					$paramstr = substr($paramstr, 0, -1);

				$ptr = 0;
				$params = array();
				while($ptr < strlen($paramstr))
				{
					if($this->debug) echo 'MODIFIER START PARAM PARSING WITH POINTER AT '.$ptr.'<br/>';
					if($this->debug) echo $paramstr.'--'.$ptr.'--'.strlen($paramstr).'--modifier<br/>';
					$params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'modifier', $ptr);
					if($this->debug) echo 'PARAM PARSED, POINTER AT '.$ptr.'<br/>';

					if($ptr >= strlen($paramstr))
						break;
					while(true)
					{
						if($paramstr[$ptr] === ' ' || $paramstr[$ptr] === '|')
						{
							if($this->debug) echo 'PARAM PARSING ENDED, " " or "|" FOUND, POINTER AT '.$ptr.'<br/>';
							$ptr++;
							break 2;
						}
						if($ptr < strlen($paramstr) && $paramstr[$ptr] === ':')
							$ptr++;
						else
							break;
					}
				}
				$cmdstrsrc = substr($cmdstrsrc, strlen($func)+1+$ptr);
				$paramstr = substr($paramstr, 0, $ptr);
				foreach($params as $k=>$p)
				{
					if(is_array($p) && is_array($p[1]))
						$state |= 2;
					else
					{
						if($state === 2 && preg_match('#^(["\'])(.+?)\1$#', $p[0], $m))
							$params[$k] = array($m[2], array('true', 'true'));
						else
							$state |= 1;
					}
				}
				if($state === 3)
					$this->errors[] = 'A function can not have named AND un-named parameters in : '.$cmdstr;
			}

			// check if we must use array_map with this plugin or not
			$mapped = false;
			if(substr($func, 0, 1) === '@')
			{
				$func = substr($func, 1);
				$mapped = true;
			}

			$pluginType = $this->getPluginType($func);

			if($state===2)
				array_unshift($params, array('value', array($output, $output)));
			else
				array_unshift($params, array($output, $output));

			if($pluginType & Dwoo::NATIVE_PLUGIN)
			{
				$params = $this->mapParams($params, null, $state);

				$params = $params['*'][0];

				$params = $this->implode_r($params);

				if($mapped)
					$output = '$this->arrayMap(\''.$func.'\', array('.$params.'))';
				else
					$output = $func.'('.$params.')';
			}
			elseif($pluginType & Dwoo::SMARTY_MODIFIER)
			{
				$params = $this->mapParams($params, null, $state);
				$params = $params['*'][0];

				$params = $this->implode_r($params);

				if($pluginType & Dwoo::CUSTOM_PLUGIN)
				{
					$callback = $this->customPlugins[$func]['callback'];
					if(is_array($callback))
					{
						if(is_object($callback[0]))
							$output = ($mapped ? '$this->arrayMap' : 'call_user_func_array').'(array($this->plugins[\''.$func.'\'][\'callback\'][0], \''.$callback[1].'\'), array('.$params.'))';
						else
							$output = ($mapped ? '$this->arrayMap' : 'call_user_func_array').'(array(\''.$callback[0].'\', \''.$callback[1].'\'), array('.$params.'))';
					}
					elseif($mapped)
						$output = '$this->arrayMap(\''.$callback.'\', array('.$params.'))';
					else
						$output = $callback.'('.$params.')';
				}
				elseif($mapped)
					$output = '$this->arrayMap(\'smarty_modifier_'.$func.'\', array('.$params.'))';
				else
					$output = 'smarty_modifier_'.$func.'('.$params.')';
			}
			else
			{
				if($pluginType & Dwoo::CUSTOM_PLUGIN)
				{
					$callback = $this->customPlugins[$func]['callback'];
					$pluginName = $callback;
				}
				else
				{
					$pluginName = 'Dwoo_Plugin_'.$func;

					if($pluginType & Dwoo::CLASS_PLUGIN)
					{
						$callback = array($pluginName, ($pluginType & Dwoo::COMPILABLE_PLUGIN) ? 'compile' : 'process');
					}
					else
					{
						$callback = $pluginName . (($pluginType & Dwoo::COMPILABLE_PLUGIN) ? '_compile' : '');
					}
				}

				$params = $this->mapParams($params, $callback, $state);

				foreach($params as &$p)
					$p = $p[0];

				if($pluginType & Dwoo::FUNC_PLUGIN)
				{
					if($pluginType & Dwoo::COMPILABLE_PLUGIN)
					{
						if($mapped)
							throw new Dwoo_Compilation_Exception('The @ operator can not be used on compiled plugins.');
						$funcCompiler = 'Dwoo_Plugin_'.$func.'_compile';
						array_unshift($params, $this);
						$output = call_user_func_array($funcCompiler, $params);
					}
					else
					{
						array_unshift($params, '$this');

						$params = $this->implode_r($params);
						if($mapped)
							$output = '$this->arrayMap(\''.$pluginName.'\', array('.$params.'))';
						else
							$output = $pluginName.'('.$params.')';
					}
				}
				else
				{
					if($pluginType & Dwoo::COMPILABLE_PLUGIN)
					{
						if($mapped)
							throw new Dwoo_Compilation_Exception('The @ operator can not be used on compiled plugins.');
						$funcCompiler = array('Dwoo_Plugin_'.$func, 'compile');
						array_unshift($params, $this);
						$output = call_user_func_array($funcCompiler, $params);
					}
					else
					{
						$params = $this->implode_r($params);

						if($pluginType & Dwoo::CUSTOM_PLUGIN)
						{
							if(is_object($callback[0]))
								$output = ($mapped ? '$this->arrayMap' : 'call_user_func_array').'(array($this->plugins[\''.$func.'\'][\'callback\'][0], \''.$callback[1].'\'), array('.$params.'))';
							else
								$output = ($mapped ? '$this->arrayMap' : 'call_user_func_array').'(array(\''.$callback[0].'\', \''.$callback[1].'\'), array('.$params.'))';
						}
						elseif($mapped)
							$output = '$this->arrayMap(array($this->getObjectPlugin(\'Dwoo_Plugin_'.$func.'\'), \'process\'), array('.$params.'))';
						else
							$output = '$this->classCall(\''.$func.'\', array('.$params.'))';
					}
				}
			}
		}

		if($curBlock === 'var' || $m[1] === null)
		{
			return $output;
		}
		elseif($curBlock === 'string' || $curBlock === 'root')
			return $m[1].'.'.$output.'.'.$m[1].(isset($add)?$add:null);
	}

	/**
	 * recursively implodes an array in a similar manner as var_export() does but with some tweaks
	 * to handle pre-compiled values and the fact that we do not need to enclose everything with
	 * "array" and do not require top-level keys to be displayed
	 *
	 * @param array $params the array to implode
	 * @param bool $recursiveCall if set to true, the function outputs key names for the top level
	 * @return string the imploded array
	 */
	protected function implode_r(array $params, $recursiveCall = false)
	{
		$out = '';
		foreach($params as $k=>$p)
		{
			if(is_array($p))
			{
				$out2 = 'array(';
				foreach($p as $k2=>$v)
					$out2 .= var_export($k2, true).' => '.(is_array($v) ? 'array('.$this->implode_r($v, true).')' : $v).', ';
				$p = rtrim($out2, ', ').')';
			}
			if($recursiveCall)
				$out .= var_export($k, true).' => '.$p.', ';
			else
				$out .= $p.', ';
		}
		return rtrim($out, ', ');
	}

	/**
	 * returns the plugin type of a plugin and adds it to the used plugins array if required
	 *
	 * @param string $name plugin name, as found in the template
	 * @return int type as a multi bit flag composed of the Dwoo plugin types constants
	 */
	protected function getPluginType($name)
	{
		$pluginType = -1;

		if(($this->securityPolicy === null && function_exists($name)) ||
			($this->securityPolicy !== null && in_array(strtolower($name), $this->securityPolicy->getAllowedPhpFunctions()) !== false))
		{
			$phpFunc = true;
		}

		while($pluginType <= 0)
		{
			if(isset($this->customPlugins[$name]))
				$pluginType = $this->customPlugins[$name]['type'] | Dwoo::CUSTOM_PLUGIN;
			elseif(class_exists('Dwoo_Plugin_'.$name, false) !== false)
			{
				if(is_subclass_of('Dwoo_Plugin_'.$name, 'Dwoo_Block_Plugin'))
					$pluginType = Dwoo::BLOCK_PLUGIN;
				else
					$pluginType = Dwoo::CLASS_PLUGIN;
				$interfaces = class_implements('Dwoo_Plugin_'.$name, false);
				if(in_array('Dwoo_ICompilable', $interfaces) !== false || in_array('Dwoo_ICompilable_Block', $interfaces) !== false)
					$pluginType |= Dwoo::COMPILABLE_PLUGIN;
			}
			elseif(function_exists('Dwoo_Plugin_'.$name) !== false)
				$pluginType = Dwoo::FUNC_PLUGIN;
			elseif(function_exists('Dwoo_Plugin_'.$name.'_compile'))
				$pluginType = Dwoo::FUNC_PLUGIN | Dwoo::COMPILABLE_PLUGIN;
			elseif(function_exists('smarty_modifier_'.$name) !== false)
				$pluginType = Dwoo::SMARTY_MODIFIER;
			elseif(function_exists('smarty_function_'.$name) !== false)
				$pluginType = Dwoo::SMARTY_FUNCTION;
			elseif(function_exists('smarty_block_'.$name) !== false)
				$pluginType = Dwoo::SMARTY_BLOCK;
			else
			{
				if($pluginType===-1)
				{
					try {
						Dwoo_Loader::loadPlugin($name, isset($phpFunc)===false);
					} catch (Exception $e) {
						if(isset($phpFunc))
							$pluginType = Dwoo::NATIVE_PLUGIN;
						else
							throw $e;
					}
				}
				else
					throw new Dwoo_Exception('Plugin "'.$name.'" could not be found');
				$pluginType++;
			}
		}

		if(($pluginType & Dwoo::COMPILABLE_PLUGIN) === 0 && ($pluginType & Dwoo::NATIVE_PLUGIN) === 0)
			$this->usedPlugins[$name] = $pluginType;

		return $pluginType;
	}

	/**
	 * handles the {strip} blocks regex replacement, do not rely on it as it will eventually be moved into it's own plugin
	 *
	 * @param array $matches the regex matches with the "1" index being the contents of the {strip} block
	 * @return string processed string
	 */
	protected function stripPreprocessorHelper(array $matches)
	{
		// TODO make this into a separated plugin
		return str_replace(array("\n","\r"), null, preg_replace('#^\s*(.+?)\s*$#m', '$1', $matches[1]));
	}

	/**
	 * runs htmlentities over the matched <?php ?> blocks when the security policy enforces that
	 *
	 * @param array $match matched php block
	 * @return string the htmlentities-converted string
	 */
	protected function phpTagEncodingHelper($match)
	{
		return htmlspecialchars($match[0]);
	}

	/**
	 * maps the parameters received from the template onto the parameters required by the given callback
	 *
	 * @param array $params the array of parameters
	 * @param callback $callback the function or method to reflect on to find out the required parameters
	 * @param int $callType the type of call in the template, 0 = no params, 1 = php-style call, 2 = named parameters call
	 * @return array parameters sorted in the correct order with missing optional parameters filled
	 */
	protected function mapParams(array $params, $callback, $callType=2)
	{
		$map = $this->getParamMap($callback);

		$paramlist = array();

		// named parameters call
		if($callType===2)
		{
			// transforms the parameter array from (x=>array('paramname'=>array(values))) to (paramname=>array(values))
			$ps = array();
			foreach($params as $p)
				$ps[$p[0]] = $p[1];

			// loops over the param map and assigns values from the template or default value for unset optional params
			while(list($k,$v) = each($map))
			{
				// "rest" array parameter, fill every remaining params in it and then break
				if($v[0] === '*')
				{
					if(count($ps) === 0)
					{
						if($v[1]===false)
							throw new Dwoo_Compilation_Exception('Rest argument missing for '.str_replace(array('Dwoo_Plugin_', '_compile'), '', (is_array($callback) ? $callback[0] : $callback)));
						else
							break;
					}
					$tmp = array();
					$tmp2 = array();
					foreach($ps as $i=>$p)
					{
						$tmp[$i] = $p[0];
						$tmp2[$i] = $p[1];
					}
					$paramlist[$v[0]] = array($tmp, $tmp2);
					unset($tmp, $tmp2, $i, $p);
					break;
				}
				// parameter is defined
				elseif(isset($ps[$v[0]]))
				{
					$paramlist[$v[0]] = $ps[$v[0]];
					unset($ps[$v[0]]);
				}
				// parameter is not defined and not optional, throw error
				elseif($v[1]===false)
					throw new Dwoo_Compilation_Exception('Argument '.$k.'/'.$v[0].' missing for '.str_replace(array('Dwoo_Plugin_', '_compile'), '', (is_array($callback) ? $callback[0] : $callback)));
				// enforce lowercased null if default value is null (php outputs NULL with var export)
				elseif($v[2]===null)
					$paramlist[$v[0]] = array('null', null);
				// outputs default value with var_export
				else
					$paramlist[$v[0]] = array(var_export($v[2], true), $v[2]);
			}
		}
		// php call or no parameter call
		elseif($callType===1||$callType===0)
		{
			// loops over the param map and assigns values from the template or default value for unset optional params
			while(list($k,$v) = each($map))
			{
				// "rest" array parameter, fill every remaining params in it and then break
				if($v[0] === '*')
				{
					if(count($params) === 0)
					{
						if($v[1]===false)
							throw new Dwoo_Compilation_Exception('Rest argument missing for '.str_replace(array('Dwoo_Plugin_', '_compile'), '', (is_array($callback) ? $callback[0] : $callback)));
						else
							break;
					}
					$tmp = array();
					$tmp2 = array();
					$i = 0;
					foreach($params as $p)
					{
						$tmp[$i] = $p[0];
						$tmp2[$i++] = $p[1];
					}
					$paramlist[$v[0]] = array($tmp, $tmp2);
					unset($tmp, $tmp2, $i, $p);
					break;
				}
				// parameter is defined
				elseif(empty($params)===false)
				{
					$paramlist[$v[0]] = array_shift($params);
				}
				// parameter is not defined and not optional, throw error
				elseif($v[1]===false)
					throw new Dwoo_Compilation_Exception('Argument '.$k.'/'.$v[0].' missing for '.str_replace(array('Dwoo_Plugin_', '_compile'), '', (is_array($callback) ? $callback[0] : $callback)));
				// enforce lowercased null if default value is null (php outputs NULL with var export)
				elseif($v[2]===null)
					$paramlist[$v[0]] = array('null', null);
				// outputs default value with var_export
				else
					$paramlist[$v[0]] = array(var_export($v[2], true), $v[2]);
			}
		}
		// parser failed miserably
		else
			throw new Dwoo_Compilation_Exception('This should not happen, please report it if you see this message');

		return $paramlist;
	}

	/**
	 * returns the parameter map of the given callback, it filters out entries typed as Dwoo and Dwoo_Compiler and turns the rest parameter into a "*"
	 *
	 * @param callback $callback the function/method to reflect on
	 * @return array processed parameter map
	 */
	protected function getParamMap($callback)
	{
		if(is_null($callback))
			return array(array('*', true));
		if(is_array($callback))
			$ref = new ReflectionMethod($callback[0], $callback[1]);
		else
			$ref = new ReflectionFunction($callback);

		$out = array();
		foreach($ref->getParameters() as $param)
		{
			if(($class = $param->getClass()) !== null && $class->name === 'Dwoo')
				continue;
			if(($class = $param->getClass()) !== null && $class->name === 'Dwoo_Compiler')
				continue;
			if($param->getName() === 'rest' && $param->isArray() === true)
				$out[] = array('*', $param->isOptional(), null);
			$out[] = array($param->getName(), $param->isOptional(), $param->isOptional() ? $param->getDefaultValue() : null);
		}

		return $out;
	}

	/**
	 * returns a default instance of this compiler, used by default by all Dwoo templates that do not have a
	 * specific compiler assigned and when you do not override the default compiler factory function
	 *
	 * @see Dwoo::setDefaultCompilerFactory()
	 * @return Dwoo_Compiler
	 */
	public static function compilerFactory()
	{
		if(self::$instance === null)
			self::$instance = new self;
		return self::$instance;
	}
}
