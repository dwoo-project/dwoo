<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE LGPLv3
 * @version   1.3.6
 * @date      2017-03-22
 * @link      http://dwoo.org/
 */

namespace Dwoo;

use Closure;
use Dwoo\Plugins\Blocks\PluginIf;
use Dwoo\Security\Exception as SecurityException;
use Dwoo\Security\Policy as SecurityPolicy;
use Dwoo\Compilation\Exception as CompilationException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Default dwoo compiler class, compiles dwoo templates into php.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class Compiler implements ICompiler
{
    /**
     * Constant that represents a php opening tag.
     * use it in case it needs to be adjusted
     *
     * @var string
     */
    const PHP_OPEN = '<?php ';

    /**
     * Constant that represents a php closing tag.
     * use it in case it needs to be adjusted
     *
     * @var string
     */
    const PHP_CLOSE = '?>';

    /**
     * Boolean flag to enable or disable debugging output.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Left script delimiter.
     *
     * @var string
     */
    protected $ld = '{';

    /**
     * Left script delimiter with escaped regex meta characters.
     *
     * @var string
     */
    protected $ldr = '\\{';

    /**
     * Right script delimiter.
     *
     * @var string
     */
    protected $rd = '}';

    /**
     * Right script delimiter with escaped regex meta characters.
     *
     * @var string
     */
    protected $rdr = '\\}';

    /**
     * Defines whether the nested comments should be parsed as nested or not.
     * defaults to false (classic block comment parsing as in all languages)
     *
     * @var bool
     */
    protected $allowNestedComments = false;

    /**
     * Defines whether opening and closing tags can contain spaces before valid data or not.
     * turn to true if you want to be sloppy with the syntax, but when set to false it allows
     * to skip javascript and css tags as long as they are in the form "{ something", which is
     * nice. default is false.
     *
     * @var bool
     */
    protected $allowLooseOpenings = false;

    /**
     * Defines whether the compiler will automatically html-escape variables or not.
     * default is false
     *
     * @var bool
     */
    protected $autoEscape = false;

    /**
     * Security policy object.
     *
     * @var SecurityPolicy
     */
    protected $securityPolicy;

    /**
     * Stores the custom plugins registered with this compiler.
     *
     * @var array
     */
    protected $customPlugins = array();

    /**
     * Stores the template plugins registered with this compiler.
     *
     * @var array
     */
    protected $templatePlugins = array();

    /**
     * Stores the pre- and post-processors callbacks.
     *
     * @var array
     */
    protected $processors = array('pre' => array(), 'post' => array());

    /**
     * Stores a list of plugins that are used in the currently compiled
     * template, and that are not compilable. these plugins will be loaded
     * during the template's runtime if required.
     * it is a 1D array formatted as key:pluginName value:pluginType
     *
     * @var array
     */
    protected $usedPlugins;

    /**
     * Stores the template undergoing compilation.
     *
     * @var string
     */
    protected $template;

    /**
     * Stores the current pointer position inside the template.
     *
     * @var int
     */
    protected $pointer;

    /**
     * Stores the current line count inside the template for debugging purposes.
     *
     * @var int
     */
    protected $line;

    /**
     * Stores the current template source while compiling it.
     *
     * @var string
     */
    protected $templateSource;

    /**
     * Stores the data within which the scope moves.
     *
     * @var array
     */
    protected $data;

    /**
     * Variable scope of the compiler, set to null if
     * it can not be resolved to a static string (i.e. if some
     * plugin defines a new scope based on a variable array key).
     *
     * @var mixed
     */
    protected $scope;

    /**
     * Variable scope tree, that allows to rebuild the current
     * scope if required, i.e. when going to a parent level.
     *
     * @var array
     */
    protected $scopeTree;

    /**
     * Block plugins stack, accessible through some methods.
     *
     * @see findBlock
     * @see getCurrentBlock
     * @see addBlock
     * @see addCustomBlock
     * @see injectBlock
     * @see removeBlock
     * @see removeTopBlock
     * @var array
     */
    protected $stack = array();

    /**
     * Current block at the top of the block plugins stack,
     * accessible through getCurrentBlock.
     *
     * @see getCurrentBlock
     * @var array
     */
    protected $curBlock;

    /**
     * Current dwoo object that uses this compiler, or null.
     *
     * @var Core
     */
    public $core;

    /**
     * Holds an instance of this class, used by getInstance when you don't
     * provide a custom compiler in order to save resources.
     *
     * @var Compiler
     */
    protected static $instance;

    /**
     * Token types.
     *
     * @var int
     */
    const T_UNQUOTED_STRING = 1;
    const T_NUMERIC         = 2;
    const T_NULL            = 4;
    const T_BOOL            = 8;
    const T_MATH            = 16;
    const T_BREAKCHAR       = 32;

    /**
     * Compiler constructor.
     * saves the created instance so that child templates get the same one
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Sets the delimiters to use in the templates.
     * delimiters can be multi-character strings but should not be one of those as they will
     * make it very hard to work with templates or might even break the compiler entirely : "\", "$", "|", ":" and
     * finally "#" only if you intend to use config-vars with the #var# syntax.
     *
     * @param string $left  left delimiter
     * @param string $right right delimiter
     */
    public function setDelimiters($left, $right)
    {
        $this->ld  = $left;
        $this->rd  = $right;
        $this->ldr = preg_quote($left, '/');
        $this->rdr = preg_quote($right, '/');
    }

    /**
     * Returns the left and right template delimiters.
     *
     * @return array containing the left and the right delimiters
     */
    public function getDelimiters()
    {
        return array($this->ld, $this->rd);
    }

    /**
     * Sets the way to handle nested comments, if set to true
     * {* foo {* some other *} comment *} will be stripped correctly.
     * if false it will remove {* foo {* some other *} and leave "comment *}" alone,
     * this is the default behavior
     *
     * @param bool $allow allow nested comments or not, defaults to true (but the default internal value is false)
     */
    public function setNestedCommentsHandling($allow = true)
    {
        $this->allowNestedComments = (bool)$allow;
    }

    /**
     * Returns the nested comments handling setting.
     *
     * @see    setNestedCommentsHandling
     * @return bool true if nested comments are allowed
     */
    public function getNestedCommentsHandling()
    {
        return $this->allowNestedComments;
    }

    /**
     * Sets the tag openings handling strictness, if set to true, template tags can
     * contain spaces before the first function/string/variable such as { $foo} is valid.
     * if set to false (default setting), { $foo} is invalid but that is however a good thing
     * as it allows css (i.e. #foo { color:red; }) to be parsed silently without triggering
     * an error, same goes for javascript.
     *
     * @param bool $allow true to allow loose handling, false to restore default setting
     */
    public function setLooseOpeningHandling($allow = false)
    {
        $this->allowLooseOpenings = (bool)$allow;
    }

    /**
     * Returns the tag openings handling strictness setting.
     *
     * @see    setLooseOpeningHandling
     * @return bool true if loose tags are allowed
     */
    public function getLooseOpeningHandling()
    {
        return $this->allowLooseOpenings;
    }

    /**
     * Changes the auto escape setting.
     * if enabled, the compiler will automatically html-escape variables,
     * unless they are passed through the safe function such as {$var|safe}
     * or {safe $var}
     * default setting is disabled/false
     *
     * @param bool $enabled set to true to enable, false to disable
     */
    public function setAutoEscape($enabled)
    {
        $this->autoEscape = (bool)$enabled;
    }

    /**
     * Returns the auto escape setting.
     * default setting is disabled/false
     *
     * @return bool
     */
    public function getAutoEscape()
    {
        return $this->autoEscape;
    }

    /**
     * Adds a preprocessor to the compiler, it will be called
     * before the template is compiled.
     *
     * @param mixed $callback either a valid callback to the preprocessor or a simple name if the autoload is set to
     *                        true
     * @param bool  $autoload if set to true, the preprocessor is auto-loaded from one of the plugin directories, else
     *                        you must provide a valid callback
     */
    public function addPreProcessor($callback, $autoload = false)
    {
        if ($autoload) {
            $name  = str_replace(Core::NAMESPACE_PLUGINS_PROCESSORS, '', Core::toCamelCase($callback));
            $class = Core::NAMESPACE_PLUGINS_PROCESSORS . $name;

            if (class_exists($class)) {
                $callback = array(new $class($this), 'process');
            } elseif (function_exists($class)) {
                $callback = $class;
            } else {
                $callback = array('autoload' => true, 'class' => $class, 'name' => $name);
            }

            $this->processors['pre'][] = $callback;
        } else {
            $this->processors['pre'][] = $callback;
        }
    }

    /**
     * Removes a preprocessor from the compiler.
     *
     * @param mixed $callback either a valid callback to the preprocessor or a simple name if it was autoloaded
     */
    public function removePreProcessor($callback)
    {
        if (($index = array_search($callback, $this->processors['pre'], true)) !== false) {
            unset($this->processors['pre'][$index]);
        } elseif (($index = array_search(Core::NAMESPACE_PLUGINS_PROCESSORS . str_replace(Core::NAMESPACE_PLUGINS_PROCESSORS, '',
                    $callback),
                $this->processors['pre'], true)) !== false) {
            unset($this->processors['pre'][$index]);
        } else {
            $class = Core::NAMESPACE_PLUGINS_PROCESSORS . str_replace(Core::NAMESPACE_PLUGINS_PROCESSORS, '', $callback);
            foreach ($this->processors['pre'] as $index => $proc) {
                if (is_array($proc) && ($proc[0] instanceof $class) || (isset($proc['class']) && $proc['class'] == $class)) {
                    unset($this->processors['pre'][$index]);
                    break;
                }
            }
        }
    }

    /**
     * Adds a postprocessor to the compiler, it will be called
     * before the template is compiled.
     *
     * @param mixed $callback either a valid callback to the postprocessor or a simple name if the autoload is set to
     *                        true
     * @param bool  $autoload if set to true, the postprocessor is auto-loaded from one of the plugin directories, else
     *                        you must provide a valid callback
     */
    public function addPostProcessor($callback, $autoload = false)
    {
        if ($autoload) {
            $name  = str_replace(Core::NAMESPACE_PLUGINS_PROCESSORS, '', $callback);
            $class = Core::NAMESPACE_PLUGINS_PROCESSORS . Core::toCamelCase($name);

            if (class_exists($class)) {
                $callback = array(new $class($this), 'process');
            } elseif (function_exists($class)) {
                $callback = $class;
            } else {
                $callback = array('autoload' => true, 'class' => $class, 'name' => $name);
            }

            $this->processors['post'][] = $callback;
        } else {
            $this->processors['post'][] = $callback;
        }
    }

    /**
     * Removes a postprocessor from the compiler.
     *
     * @param mixed $callback either a valid callback to the postprocessor or a simple name if it was autoloaded
     */
    public function removePostProcessor($callback)
    {
        if (($index = array_search($callback, $this->processors['post'], true)) !== false) {
            unset($this->processors['post'][$index]);
        } elseif (($index = array_search(Core::NAMESPACE_PLUGINS_PROCESSORS . str_replace(Core::NAMESPACE_PLUGINS_PROCESSORS, '',
                    $callback),
                $this->processors['post'], true)) !== false) {
            unset($this->processors['post'][$index]);
        } else {
            $class = Core::NAMESPACE_PLUGINS_PROCESSORS . str_replace(Core::NAMESPACE_PLUGINS_PROCESSORS, '', $callback);
            foreach ($this->processors['post'] as $index => $proc) {
                if (is_array($proc) && ($proc[0] instanceof $class) || (isset($proc['class']) && $proc['class'] == $class)) {
                    unset($this->processors['post'][$index]);
                    break;
                }
            }
        }
    }

    /**
     * Internal function to autoload processors at runtime if required.
     *
     * @param string $class the class/function name
     * @param string $name  the plugin name (without Dwoo_Plugin_ prefix)
     *
     * @return array|string
     * @throws Exception
     */
    protected function loadProcessor($class, $name)
    {
        if (!class_exists($class) && !function_exists($class)) {
            try {
                $this->getCore()->getLoader()->loadPlugin($name);
            }
            catch (Exception $e) {
                throw new Exception('Processor ' . $name . ' could not be found in your plugin directories, please ensure it is in a file named ' . $name . '.php in the plugin directory');
            }
        }

        if (class_exists($class)) {
            return array(new $class($this), 'process');
        }

        if (function_exists($class)) {
            return $class;
        }

        throw new Exception('Wrong processor name, when using autoload the processor must be in one of your plugin dir as "name.php" containg a class or function named "Dwoo_Processor_name"');
    }

    /**
     * Adds an used plugin, this is reserved for use by the {template} plugin.
     * this is required so that plugin loading bubbles up from loaded
     * template files to the current one
     *
     * @private
     *
     * @param string $name function name
     * @param int    $type plugin type (Core::*_PLUGIN)
     */
    public function addUsedPlugin($name, $type)
    {
        $this->usedPlugins[$name] = $type;
    }

    /**
     * Returns all the plugins this template uses.
     *
     * @private
     * @return  array the list of used plugins in the parsed template
     */
    public function getUsedPlugins()
    {
        return $this->usedPlugins;
    }

    /**
     * Adds a template plugin, this is reserved for use by the {template} plugin.
     * this is required because the template functions are not declared yet
     * during compilation, so we must have a way of validating their argument
     * signature without using the reflection api
     *
     * @private
     *
     * @param string $name   function name
     * @param array  $params parameter array to help validate the function call
     * @param string $uuid   unique id of the function
     * @param string $body   function php code
     */
    public function addTemplatePlugin($name, array $params, $uuid, $body = null)
    {
        $this->templatePlugins[$name] = array('params' => $params, 'body' => $body, 'uuid' => $uuid);
    }

    /**
     * Returns all the parsed sub-templates.
     *
     * @private
     * @return  array the parsed sub-templates
     */
    public function getTemplatePlugins()
    {
        return $this->templatePlugins;
    }

    /**
     * Marks a template plugin as being called, which means its source must be included in the compiled template.
     *
     * @param string $name function name
     */
    public function useTemplatePlugin($name)
    {
        $this->templatePlugins[$name]['called'] = true;
    }

    /**
     * Adds the custom plugins loaded into Dwoo to the compiler so it can load them.
     *
     * @see Core::addPlugin
     *
     * @param array $customPlugins an array of custom plugins
     */
    public function setCustomPlugins(array $customPlugins)
    {
        $this->customPlugins = $customPlugins;
    }

    /**
     * Sets the security policy object to enforce some php security settings.
     * use this if untrusted persons can modify templates,
     * set it on the Dwoo object as it will be passed onto the compiler automatically
     *
     * @param SecurityPolicy $policy the security policy object
     */
    public function setSecurityPolicy(SecurityPolicy $policy = null)
    {
        $this->securityPolicy = $policy;
    }

    /**
     * Returns the current security policy object or null by default.
     *
     * @return SecurityPolicy|null the security policy object if any
     */
    public function getSecurityPolicy()
    {
        return $this->securityPolicy;
    }

    /**
     * Sets the pointer position.
     *
     * @param int  $position the new pointer position
     * @param bool $isOffset if set to true, the position acts as an offset and not an absolute position
     */
    public function setPointer($position, $isOffset = false)
    {
        if ($isOffset) {
            $this->pointer += $position;
        } else {
            $this->pointer = $position;
        }
    }

    /**
     * Returns the current pointer position, only available during compilation of a template.
     *
     * @return int
     */
    public function getPointer()
    {
        return $this->pointer;
    }

    /**
     * Sets the line number.
     *
     * @param int  $number   the new line number
     * @param bool $isOffset if set to true, the position acts as an offset and not an absolute position
     */
    public function setLine($number, $isOffset = false)
    {
        if ($isOffset) {
            $this->line += $number;
        } else {
            $this->line = $number;
        }
    }

    /**
     * Returns the current line number, only available during compilation of a template.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Returns the dwoo object that initiated this template compilation, only available during compilation of a
     * template.
     *
     * @return Core
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * Overwrites the template that is being compiled.
     *
     * @param string $newSource   the template source that must replace the current one
     * @param bool   $fromPointer if set to true, only the source from the current pointer position is replaced
     *
     * @return void
     */
    public function setTemplateSource($newSource, $fromPointer = false)
    {
        if ($fromPointer === true) {
            $this->templateSource = substr($this->templateSource, 0, $this->pointer) . $newSource;
        } else {
            $this->templateSource = $newSource;
        }
    }

    /**
     * Returns the template that is being compiled.
     *
     * @param mixed $fromPointer if set to true, only the source from the current pointer
     *                           position is returned, if a number is given it overrides the current pointer
     *
     * @return string the template or partial template
     */
    public function getTemplateSource($fromPointer = false)
    {
        if ($fromPointer === true) {
            return substr($this->templateSource, $this->pointer);
        } elseif (is_numeric($fromPointer)) {
            return substr($this->templateSource, $fromPointer);
        } else {
            return $this->templateSource;
        }
    }

    /**
     * Resets the compilation pointer, effectively restarting the compilation process.
     * this is useful if a plugin modifies the template source since it might need to be recompiled
     */
    public function recompile()
    {
        $this->setPointer(0);
    }

    /**
     * Compiles the provided string down to php code.
     *
     * @param Core      $core
     * @param ITemplate $template the template to compile
     *
     * @return string a compiled php string
     * @throws CompilationException
     */
    public function compile(Core $core, ITemplate $template)
    {
        // init vars
        //		$compiled = '';
        $tpl                  = $template->getSource();
        $ptr                  = 0;
        $this->core           = $core;
        $this->template       = $template;
        $this->templateSource = &$tpl;
        $this->pointer        = &$ptr;

        while (true) {
            // if pointer is at the beginning, reset everything, that allows a plugin to externally reset the compiler if everything must be reparsed
            if ($ptr === 0) {
                // resets variables
                $this->usedPlugins     = array();
                $this->data            = array();
                $this->scope           = &$this->data;
                $this->scopeTree       = array();
                $this->stack           = array();
                $this->line            = 1;
                $this->templatePlugins = array();
                // add top level block
                $compiled                 = $this->addBlock('TopLevelBlock', array(), 0);
                $this->stack[0]['buffer'] = '';

                if ($this->debug) {
                    echo "\n";
                    echo 'COMPILER INIT' . "\n";
                }

                if ($this->debug) {
                    echo 'PROCESSING PREPROCESSORS (' . count($this->processors['pre']) . ')' . "\n";
                }

                // runs preprocessors
                foreach ($this->processors['pre'] as $preProc) {
                    if (is_array($preProc) && isset($preProc['autoload'])) {
                        $preProc = $this->loadProcessor($preProc['class'], $preProc['name']);
                    }
                    if (is_array($preProc) && $preProc[0] instanceof Processor) {
                        $tpl = call_user_func($preProc, $tpl);
                    } else {
                        $tpl = call_user_func($preProc, $this, $tpl);
                    }
                }
                unset($preProc);

                // show template source if debug
                if ($this->debug) {
                    echo '<pre>'.print_r(htmlentities($tpl), true).'</pre>'."\n";
                }

                // strips php tags if required by the security policy
                if ($this->securityPolicy !== null) {
                    $search = array('{<\?php.*?\?>}');
                    if (ini_get('short_open_tags')) {
                        $search = array('{<\?.*?\?>}', '{<%.*?%>}');
                    }
                    switch ($this->securityPolicy->getPhpHandling()) {
                        case SecurityPolicy::PHP_ALLOW:
                            break;
                        case SecurityPolicy::PHP_ENCODE:
                            $tpl = preg_replace_callback($search, array($this, 'phpTagEncodingHelper'), $tpl);
                            break;
                        case SecurityPolicy::PHP_REMOVE:
                            $tpl = preg_replace($search, '', $tpl);
                    }
                }
            }

            $pos = strpos($tpl, $this->ld, $ptr);

            if ($pos === false) {
                $this->push(substr($tpl, $ptr), 0);
                break;
            } elseif (substr($tpl, $pos - 1, 1) === '\\' && substr($tpl, $pos - 2, 1) !== '\\') {
                $this->push(substr($tpl, $ptr, $pos - $ptr - 1) . $this->ld);
                $ptr = $pos + strlen($this->ld);
            } elseif (preg_match('/^' . $this->ldr . ($this->allowLooseOpenings ? '\s*' : '') . 'literal' . ($this->allowLooseOpenings ? '\s*' : '') . $this->rdr . '/s', substr($tpl, $pos), $litOpen)) {
                if (!preg_match('/' . $this->ldr . ($this->allowLooseOpenings ? '\s*' : '') . '\/literal' . ($this->allowLooseOpenings ? '\s*' : '') . $this->rdr . '/s', $tpl, $litClose, PREG_OFFSET_CAPTURE, $pos)) {
                    throw new CompilationException($this, 'The {literal} blocks must be closed explicitly with {/literal}');
                }
                $endpos = $litClose[0][1];
                $this->push(substr($tpl, $ptr, $pos - $ptr) . substr($tpl, $pos + strlen($litOpen[0]), $endpos - $pos - strlen($litOpen[0])));
                $ptr = $endpos + strlen($litClose[0][0]);
            } else {
                if (substr($tpl, $pos - 2, 1) === '\\' && substr($tpl, $pos - 1, 1) === '\\') {
                    $this->push(substr($tpl, $ptr, $pos - $ptr - 1));
                    $ptr = $pos;
                }

                $this->push(substr($tpl, $ptr, $pos - $ptr));
                $ptr = $pos;

                $pos += strlen($this->ld);
                if ($this->allowLooseOpenings) {
                    while (substr($tpl, $pos, 1) === ' ') {
                        $pos += 1;
                    }
                } else {
                    if (substr($tpl, $pos, 1) === ' ' || substr($tpl, $pos, 1) === "\r" || substr($tpl, $pos, 1) === "\n" || substr($tpl, $pos, 1) === "\t") {
                        $ptr = $pos;
                        $this->push($this->ld);
                        continue;
                    }
                }

                // check that there is an end tag present
                if (strpos($tpl, $this->rd, $pos) === false) {
                    throw new CompilationException($this, 'A template tag was not closed, started with "' . substr($tpl, $ptr, 30) . '"');
                }

                $ptr += strlen($this->ld);
                $subptr = $ptr;

                while (true) {
                    $parsed = $this->parse($tpl, $subptr, null, false, 'root', $subptr);

                    // reload loop if the compiler was reset
                    if ($ptr === 0) {
                        continue 2;
                    }

                    $len = $subptr - $ptr;
                    $this->push($parsed, substr_count(substr($tpl, $ptr, $len), "\n"));
                    $ptr += $len;

                    if ($parsed === false) {
                        break;
                    }
                }
            }
        }

        $compiled .= $this->removeBlock('TopLevelBlock');

        if ($this->debug) {
            echo 'PROCESSING POSTPROCESSORS' . "\n";
        }

        foreach ($this->processors['post'] as $postProc) {
            if (is_array($postProc) && isset($postProc['autoload'])) {
                $postProc = $this->loadProcessor($postProc['class'], $postProc['name']);
            }
            if (is_array($postProc) && $postProc[0] instanceof Processor) {
                $compiled = call_user_func($postProc, $compiled);
            } else {
                $compiled = call_user_func($postProc, $this, $compiled);
            }
        }
        unset($postProc);

        if ($this->debug) {
            echo 'COMPILATION COMPLETE : MEM USAGE : ' . memory_get_usage() . "\n";
        }

        $output = "<?php\n/* template head */\n";

        // build plugin preloader
        foreach ($this->getUsedPlugins() as $plugin => $type) {
            if ($type & Core::CUSTOM_PLUGIN) {
                continue;
            }

            switch ($type) {
                case Core::CLASS_PLUGIN:
                case Core::CLASS_PLUGIN + Core::BLOCK_PLUGIN:
                    if (class_exists('Plugin' . $plugin) !== false) {
                        $output .= "if (class_exists('" . "Plugin" . $plugin . "')===false)".
                        "\n\t\$this->getLoader()->loadPlugin('Plugin$plugin');\n";
                    } else {
                        $output .= "if (class_exists('" . Core::NAMESPACE_PLUGINS_BLOCKS . "Plugin" . $plugin . "')===false)".
                        "\n\t\$this->getLoader()->loadPlugin('Plugin$plugin');\n";
                    }
                    break;
                case Core::CLASS_PLUGIN + Core::FUNC_PLUGIN:
                    if (class_exists('Plugin' . $plugin) !== false) {
                        $output .= "if (class_exists('" . "Plugin" . $plugin . "')===false)".
                            "\n\t\$this->getLoader()->loadPlugin('Plugin$plugin');\n";
                    } else {
                        $output .= "if (class_exists('" . Core::NAMESPACE_PLUGINS_FUNCTIONS . "Plugin" . $plugin . "')===false)".
                            "\n\t\$this->getLoader()->loadPlugin('Plugin$plugin');\n";
                    }
                    break;
                case Core::FUNC_PLUGIN:
                    if (function_exists('Plugin' . $plugin) !== false) {
                        $output .= "if (function_exists('" . "Plugin" . $plugin . "')===false)".
                        "\n\t\$this->getLoader()->loadPlugin('Plugin$plugin');\n";
                    } else {
                        $output .= "if (function_exists('" . Core::NAMESPACE_PLUGINS_FUNCTIONS . "Plugin" . $plugin . "')===false)".
                        "\n\t\$this->getLoader()->loadPlugin('Plugin$plugin');\n";
                    }
                    break;
                case Core::SMARTY_MODIFIER:
                    $output .= "if (function_exists('smarty_modifier_$plugin')===false)".
                    "\n\t\$this->getLoader()->loadPlugin('$plugin');\n";
                    break;
                case Core::SMARTY_FUNCTION:
                    $output .= "if (function_exists('smarty_function_$plugin')===false)".
                    "\n\t\$this->getLoader()->loadPlugin('$plugin');\n";
                    break;
                case Core::SMARTY_BLOCK:
                    $output .= "if (function_exists('smarty_block_$plugin')===false)".
                    "\n\t\$this->getLoader()->loadPlugin('$plugin');\n";
                    break;
                case Core::PROXY_PLUGIN:
                    $output .= $this->getCore()->getPluginProxy()->getLoader($plugin);
                    break;
                default:
                    throw new CompilationException($this, 'Type error for ' . $plugin . ' with type' . $type);
            }
        }

        foreach ($this->templatePlugins as $function => $attr) {
            if (isset($attr['called']) && $attr['called'] === true && !isset($attr['checked'])) {
                $this->resolveSubTemplateDependencies($function);
            }
        }
        foreach ($this->templatePlugins as $function) {
            if (isset($function['called']) && $function['called'] === true) {
                $output .= $function['body'] . PHP_EOL;
            }
        }

        $output .= $compiled . "\n?>";

        $output = preg_replace('/(?<!;|\}|\*\/|\n|\{)(\s*' . preg_quote(self::PHP_CLOSE, '/') . preg_quote(self::PHP_OPEN, '/') . ')/', ";\n", $output);
        $output = str_replace(self::PHP_CLOSE . self::PHP_OPEN, "\n", $output);

        // handle <?xml tag at the beginning
        $output = preg_replace('#(/\* template body \*/ \?>\s*)<\?xml#is', '$1<?php echo \'<?xml\'; ?>', $output);

        // add another line break after PHP closing tags that have a line break following,
        // as we do not know whether it's intended, and PHP will strip it otherwise
        $output = preg_replace('/(?<!"|<\?xml)\s*\?>\n/', '$0' . "\n", $output);

        if ($this->debug) {
            echo '=============================================================================================' . "\n";
            $lines = preg_split('{\r\n|\n|<br />}', $output);
            array_shift($lines);
            foreach ($lines as $i => $line) {
                echo ($i + 1) . '. ' . $line . "\r\n";
            }
            echo '=============================================================================================' . "\n";
        }

        $this->template = $this->dwoo = null;
        $tpl            = null;

        return $output;
    }

    /**
     * Checks what sub-templates are used in every sub-template so that we're sure they are all compiled.
     *
     * @param string $function the sub-template name
     */
    protected function resolveSubTemplateDependencies($function)
    {
        if ($this->debug) {
            echo 'Compiler::' . __FUNCTION__ . "\n";
        }

        $body = $this->templatePlugins[$function]['body'];
        foreach ($this->templatePlugins as $func => $attr) {
            if ($func !== $function && !isset($attr['called']) && strpos($body, Core::NAMESPACE_PLUGINS_FUNCTIONS .
            'Plugin' . Core::toCamelCase($func)) !== false) {
                $this->templatePlugins[$func]['called'] = true;
                $this->resolveSubTemplateDependencies($func);
            }
        }
        $this->templatePlugins[$function]['checked'] = true;
    }

    /**
     * Adds compiled content to the current block.
     *
     * @param string $content   the content to push
     * @param int    $lineCount newlines count in content, optional
     *
     * @throws CompilationException
     */
    public function push($content, $lineCount = null)
    {
        if ($lineCount === null) {
            $lineCount = substr_count($content, "\n");
        }

        if ($this->curBlock['buffer'] === null && count($this->stack) > 1) {
            // buffer is not initialized yet (the block has just been created)
            $this->stack[count($this->stack) - 2]['buffer'] .= (string)$content;
            $this->curBlock['buffer'] = '';
        } else {
            if (!isset($this->curBlock['buffer'])) {
                throw new CompilationException($this, 'The template has been closed too early, you probably have an extra block-closing tag somewhere');
            }
            // append current content to current block's buffer
            $this->curBlock['buffer'] .= (string)$content;
        }
        $this->line += $lineCount;
    }

    /**
     * Sets the scope.
     * set to null if the scope becomes "unstable" (i.e. too variable or unknown) so that
     * variables are compiled in a more evaluative way than just $this->scope['key']
     *
     * @param mixed $scope    a string i.e. "level1.level2" or an array i.e. array("level1", "level2")
     * @param bool  $absolute if true, the scope is set from the top level scope and not from the current scope
     *
     * @return array the current scope tree
     */
    public function setScope($scope, $absolute = false)
    {
        $old = $this->scopeTree;

        if ($scope === null) {
            unset($this->scope);
            $this->scope = null;
        }

        if (is_array($scope) === false) {
            $scope = explode('.', $scope);
        }

        if ($absolute === true) {
            $this->scope     = &$this->data;
            $this->scopeTree = array();
        }

        while (($bit = array_shift($scope)) !== null) {
            if ($bit === '_parent' || $bit === '_') {
                array_pop($this->scopeTree);
                reset($this->scopeTree);
                $this->scope = &$this->data;
                $cnt         = count($this->scopeTree);
                for ($i = 0; $i < $cnt; ++ $i) {
                    $this->scope = &$this->scope[$this->scopeTree[$i]];
                }
            } elseif ($bit === '_root' || $bit === '__') {
                $this->scope     = &$this->data;
                $this->scopeTree = array();
            } elseif (isset($this->scope[$bit])) {
                $this->scope       = &$this->scope[$bit];
                $this->scopeTree[] = $bit;
            } else {
                $this->scope[$bit] = array();
                $this->scope       = &$this->scope[$bit];
                $this->scopeTree[] = $bit;
            }
        }

        return $old;
    }

    /**
     * Adds a block to the top of the block stack.
     *
     * @param string $type      block type (name)
     * @param array  $params    the parameters array
     * @param int    $paramtype the parameters type (see mapParams), 0, 1 or 2
     *
     * @return string the preProcessing() method's output
     */
    public function addBlock($type, array $params, $paramtype)
    {
        if ($this->debug) {
            echo 'Compiler::' . __FUNCTION__ . "\n";
        }

        $class = Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($type);
        if (class_exists($class) === false) {
            $this->getCore()->getLoader()->loadPlugin($type);
        }
        $params = $this->mapParams($params, array($class, 'init'), $paramtype);

        $this->stack[]  = array(
            'type'   => $type,
            'params' => $params,
            'custom' => false,
            'class'  => $class,
            'buffer' => null
        );
        $this->curBlock = &$this->stack[count($this->stack) - 1];

        return call_user_func(array($class, 'preProcessing'), $this, $params, '', '', $type);
    }

    /**
     * Adds a custom block to the top of the block stack.
     *
     * @param string $type      block type (name)
     * @param array  $params    the parameters array
     * @param int    $paramtype the parameters type (see mapParams), 0, 1 or 2
     *
     * @return string the preProcessing() method's output
     */
    public function addCustomBlock($type, array $params, $paramtype)
    {
        $callback = $this->customPlugins[$type]['callback'];
        if (is_array($callback)) {
            $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
        } else {
            $class = $callback;
        }

        $params = $this->mapParams($params, array($class, 'init'), $paramtype);

        $this->stack[]  = array(
            'type'   => $type,
            'params' => $params,
            'custom' => true,
            'class'  => $class,
            'buffer' => null
        );
        $this->curBlock = &$this->stack[count($this->stack) - 1];

        return call_user_func(array($class, 'preProcessing'), $this, $params, '', '', $type);
    }

    /**
     * Injects a block at the top of the plugin stack without calling its preProcessing method.
     * used by {else} blocks to re-add themselves after having closed everything up to their parent
     *
     * @param string $type   block type (name)
     * @param array  $params parameters array
     */
    public function injectBlock($type, array $params)
    {
        if ($this->debug) {
            echo 'Compiler::' . __FUNCTION__ . "\n";
        }

        $class = Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($type);
        if (class_exists($class) === false) {
            $this->getCore()->getLoader()->loadPlugin($type);
        }
        $this->stack[]  = array(
            'type'   => $type,
            'params' => $params,
            'custom' => false,
            'class'  => $class,
            'buffer' => null
        );
        $this->curBlock = &$this->stack[count($this->stack) - 1];
    }

    /**
     * Removes the closest-to-top block of the given type and all other
     * blocks encountered while going down the block stack.
     *
     * @param string $type block type (name)
     *
     * @return string the output of all postProcessing() method's return values of the closed blocks
     * @throws CompilationException
     */
    public function removeBlock($type)
    {
        if ($this->debug) {
            echo 'Compiler::' . __FUNCTION__ . "\n";
        }

        $output = '';

        $pluginType = $this->getPluginType($type);
        if ($pluginType & Core::SMARTY_BLOCK) {
            $type = 'Smartyinterface';
        }
        while (true) {
            while ($top = array_pop($this->stack)) {
                if ($top['custom']) {
                    $class = $top['class'];
                } else {
                    $class = Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($top['type']);
                }
                if (count($this->stack)) {
                    $this->curBlock = &$this->stack[count($this->stack) - 1];
                    $this->push(call_user_func(array(
                        $class,
                        'postProcessing'
                    ), $this, $top['params'], '', '', $top['buffer']), 0);
                } else {
                    $null           = null;
                    $this->curBlock = &$null;
                    $output         = call_user_func(
                        array(
                        $class,
                        'postProcessing'
                        ), $this, $top['params'], '', '', $top['buffer']
                    );
                }

                if ($top['type'] === $type) {
                    break 2;
                }
            }

            throw new CompilationException($this, 'Syntax malformation, a block of type "' . $type . '" was closed but was not opened');
        }

        return $output;
    }

    /**
     * Returns a reference to the first block of the given type encountered and
     * optionally closes all blocks until it finds it
     * this is mainly used by {else} plugins to close everything that was opened
     * between their parent and themselves.
     *
     * @param string $type       the block type (name)
     * @param bool   $closeAlong whether to close all blocks encountered while going down the block stack or not
     *
     * @return mixed &array the array is as such: array('type'=>pluginName, 'params'=>parameter array,
     *               'custom'=>bool defining whether it's a custom plugin or not, for internal use)
     * @throws CompilationException
     */
    public function &findBlock($type, $closeAlong = false)
    {
        if ($closeAlong === true) {
            while ($b = end($this->stack)) {
                if ($b['type'] === $type) {
                    return $this->stack[key($this->stack)];
                }
                $this->push($this->removeTopBlock(), 0);
            }
        } else {
            end($this->stack);
            while ($b = current($this->stack)) {
                if ($b['type'] === $type) {
                    return $this->stack[key($this->stack)];
                }
                prev($this->stack);
            }
        }

        throw new CompilationException($this, 'A parent block of type "' . $type . '" is required and can not be found');
    }

    /**
     * Returns a reference to the current block array.
     *
     * @return array the array is as such: array('type'=>pluginName, 'params'=>parameter array,
     *                'custom'=>bool defining whether it's a custom plugin or not, for internal use)
     */
    public function &getCurrentBlock()
    {
        return $this->curBlock;
    }

    /**
     * Removes the block at the top of the stack and calls its postProcessing() method.
     *
     * @return string the postProcessing() method's output
     * @throws CompilationException
     */
    public function removeTopBlock()
    {
        if ($this->debug) {
            echo 'Compiler::' . __FUNCTION__ . "\n";
        }

        $o = array_pop($this->stack);
        if ($o === null) {
            throw new CompilationException($this, 'Syntax malformation, a block of unknown type was closed but was not opened.');
        }
        if ($o['custom']) {
            $class = $o['class'];
        } else {
            $class = Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($o['type']);
        }

        $this->curBlock = &$this->stack[count($this->stack) - 1];

        return call_user_func(array($class, 'postProcessing'), $this, $o['params'], '', '', $o['buffer']);
    }

    /**
     * Returns the compiled parameters (for example a variable's compiled parameter will be "$this->scope['key']") out
     * of the given parameter array.
     *
     * @param array $params parameter array
     *
     * @return array filtered parameters
     */
    public function getCompiledParams(array $params)
    {
        foreach ($params as $k => $p) {
            if (is_array($p)) {
                $params[$k] = $p[0];
            }
        }

        return $params;
    }

    /**
     * Returns the real parameters (for example a variable's real parameter will be its key, etc) out of the given
     * parameter array.
     *
     * @param array $params parameter array
     *
     * @return array filtered parameters
     */
    public function getRealParams(array $params)
    {
        foreach ($params as $k => $p) {
            if (is_array($p)) {
                $params[$k] = $p[1];
            }
        }

        return $params;
    }

    /**
     * Returns the token of each parameter out of the given parameter array.
     *
     * @param array $params parameter array
     *
     * @return array tokens
     */
    public function getParamTokens(array $params)
    {
        foreach ($params as $k => $p) {
            if (is_array($p)) {
                $params[$k] = isset($p[2]) ? $p[2] : 0;
            }
        }

        return $params;
    }

    /**
     * Entry point of the parser, it redirects calls to other parse* functions.
     *
     * @param string $in            the string within which we must parse something
     * @param int    $from          the starting offset of the parsed area
     * @param int    $to            the ending offset of the parsed area
     * @param mixed  $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by
     *                              default
     * @param string $curBlock      the current parser-block being processed
     * @param mixed  $pointer       a reference to a pointer that will be increased by the amount of characters parsed,
     *                              or null by default
     *
     * @return string parsed values
     * @throws CompilationException
     */
    protected function parse($in, $from, $to, $parsingParams = false, $curBlock = '', &$pointer = null)
    {
        if ($this->debug) {
            echo 'Compiler::' . __FUNCTION__ . "\n";
        }

        if ($to === null) {
            $to = strlen($in);
        }
        $first = substr($in, $from, 1);

        if ($first === false) {
            throw new CompilationException($this, 'Unexpected EOF, a template tag was not closed');
        }

        while ($first === ' ' || $first === "\n" || $first === "\t" || $first === "\r") {
            if ($curBlock === 'root' && substr($in, $from, strlen($this->rd)) === $this->rd) {
                // end template tag
                $pointer += strlen($this->rd);
                if ($this->debug) {
                    echo 'TEMPLATE PARSING ENDED' . "\n";
                }

                return false;
            }
            ++ $from;
            if ($pointer !== null) {
                ++ $pointer;
            }
            if ($from >= $to) {
                if (is_array($parsingParams)) {
                    return $parsingParams;
                } else {
                    return '';
                }
            }
            $first = $in[$from];
        }

        $substr = substr($in, $from, $to - $from);

        if ($this->debug) {
            echo 'PARSE CALL : PARSING "' . htmlentities(substr($in, $from, min($to - $from, 50))) . (($to - $from) > 50 ? '...' : '') . '" @ ' . $from . ':' . $to . ' in ' . $curBlock . ' : pointer=' . $pointer . "\n";
        }
        $parsed = '';

        if ($curBlock === 'root' && $first === '*') {
            $src      = $this->getTemplateSource();
            $startpos = $this->getPointer() - strlen($this->ld);
            if (substr($src, $startpos, strlen($this->ld)) === $this->ld) {
                if ($startpos > 0) {
                    do {
                        $char = substr($src, -- $startpos, 1);
                        if ($char == "\n") {
                            ++ $startpos;
                            $whitespaceStart = true;
                            break;
                        }
                    }
                    while ($startpos > 0 && ($char == ' ' || $char == "\t"));
                }

                if (!isset($whitespaceStart)) {
                    $startpos = $this->getPointer();
                } else {
                    $pointer -= $this->getPointer() - $startpos;
                }

                if ($this->allowNestedComments && strpos($src, $this->ld . '*', $this->getPointer()) !== false) {
                    $comOpen  = $this->ld . '*';
                    $comClose = '*' . $this->rd;
                    $level    = 1;
                    $ptr      = $this->getPointer();

                    while ($level > 0 && $ptr < strlen($src)) {
                        $open  = strpos($src, $comOpen, $ptr);
                        $close = strpos($src, $comClose, $ptr);

                        if ($open !== false && $close !== false) {
                            if ($open < $close) {
                                $ptr = $open + strlen($comOpen);
                                ++ $level;
                            } else {
                                $ptr = $close + strlen($comClose);
                                -- $level;
                            }
                        } elseif ($open !== false) {
                            $ptr = $open + strlen($comOpen);
                            ++ $level;
                        } elseif ($close !== false) {
                            $ptr = $close + strlen($comClose);
                            -- $level;
                        } else {
                            $ptr = strlen($src);
                        }
                    }
                    $endpos = $ptr - strlen('*' . $this->rd);
                } else {
                    $endpos = strpos($src, '*' . $this->rd, $startpos);
                    if ($endpos == false) {
                        throw new CompilationException($this, 'Un-ended comment');
                    }
                }
                $pointer += $endpos - $startpos + strlen('*' . $this->rd);
                if (isset($whitespaceStart) && preg_match('#^[\t ]*\r?\n#', substr($src, $endpos + strlen('*' . $this->rd)), $m)) {
                    $pointer += strlen($m[0]);
                    $this->curBlock['buffer'] = substr($this->curBlock['buffer'], 0, strlen($this->curBlock['buffer']) - ($this->getPointer() - $startpos - strlen($this->ld)));
                }

                return false;
            }
        }

        if ($first === '$') {
            // var
            $out    = $this->parseVar($in, $from, $to, $parsingParams, $curBlock, $pointer);
            $parsed = 'var';
        } elseif ($first === '%' && preg_match('#^%[a-z_\\\\]#i', $substr)) {
            // Short constant
            $out = $this->parseConst($in, $from, $to, $parsingParams, $curBlock, $pointer);
        } elseif (($first === '"' || $first === "'") && !(is_array($parsingParams) && preg_match('#^([\'"])[a-z0-9_]+\1\s*=>?(?:\s+|[^=])#i', $substr))) {
            // string
            $out = $this->parseString($in, $from, $to, $parsingParams, $curBlock, $pointer);
        } elseif (preg_match('/^\\\\?[a-z_](?:\\\\?[a-z0-9_]+)*(?:::[a-z_][a-z0-9_]*)?(' . (is_array($parsingParams) || $curBlock != 'root' ? '' : '\s+[^(]|') . '\s*\(|\s*' . $this->rdr . '|\s*;)/i', $substr)) {
            // func
            $out    = $this->parseFunction($in, $from, $to, $parsingParams, $curBlock, $pointer);
            $parsed = 'func';
        } elseif ($first === ';') {
            // instruction end
            if ($this->debug) {
                echo 'END OF INSTRUCTION' . "\n";
            }
            if ($pointer !== null) {
                ++ $pointer;
            }

            return $this->parse($in, $from + 1, $to, false, 'root', $pointer);
        } elseif ($curBlock === 'root' && preg_match('#^/([a-z_][a-z0-9_]*)?#i', $substr, $match)) {
            // close block
            if (!empty($match[1]) && $match[1] == 'else') {
                throw new CompilationException($this, 'Else blocks must not be closed explicitly, they are automatically closed when their parent block is closed');
            }
            if (!empty($match[1]) && $match[1] == 'elseif') {
                throw new CompilationException($this, 'Elseif blocks must not be closed explicitly, they are automatically closed when their parent block is closed or a new else/elseif block is declared after them');
            }
            if ($pointer !== null) {
                $pointer += strlen($match[0]);
            }
            if (empty($match[1])) {
                if ($this->curBlock['type'] == 'else' || $this->curBlock['type'] == 'elseif') {
                    $pointer -= strlen($match[0]);
                }
                if ($this->debug) {
                    echo 'TOP BLOCK CLOSED' . "\n";
                }

                return $this->removeTopBlock();
            } else {
                if ($this->debug) {
                    echo 'BLOCK OF TYPE ' . $match[1] . ' CLOSED' . "\n";
                }

                return $this->removeBlock($match[1]);
            }
        } elseif ($curBlock === 'root' && substr($substr, 0, strlen($this->rd)) === $this->rd) {
            // end template tag
            if ($this->debug) {
                echo 'TAG PARSING ENDED' . "\n";
            }
            $pointer += strlen($this->rd);

            return false;
        } elseif (is_array($parsingParams) && preg_match('#^(([\'"]?)[a-z0-9_]+\2\s*=' . ($curBlock === 'array' ? '>?' : '') . ')(?:\s+|[^=]).*#i', $substr, $match)) {
            // named parameter
            if ($this->debug) {
                echo 'NAMED PARAM FOUND' . "\n";
            }
            $len = strlen($match[1]);
            while (substr($in, $from + $len, 1) === ' ') {
                ++ $len;
            }
            if ($pointer !== null) {
                $pointer += $len;
            }

            $output = array(
                trim($match[1], " \t\r\n=>'\""),
                $this->parse($in, $from + $len, $to, false, 'namedparam', $pointer)
            );

            $parsingParams[] = $output;

            return $parsingParams;
        } elseif (preg_match('#^(\\\\?[a-z_](?:\\\\?[a-z0-9_]+)*::\$[a-z0-9_]+)#i', $substr, $match)) {
            // static member access
            $parsed = 'var';
            if (is_array($parsingParams)) {
                $parsingParams[] = array($match[1], $match[1]);
                $out             = $parsingParams;
            } else {
                $out = $match[1];
            }
            $pointer += strlen($match[1]);
        } elseif ($substr !== '' && (is_array($parsingParams) || $curBlock === 'namedparam' || $curBlock === 'condition' || $curBlock === 'expression')) {
            // unquoted string, bool or number
            $out = $this->parseOthers($in, $from, $to, $parsingParams, $curBlock, $pointer);
        } else {
            // parse error
            throw new CompilationException($this, 'Parse error in "' . substr($in, $from, $to - $from) . '"');
        }

        if (empty($out)) {
            return '';
        }

        $substr = substr($in, $pointer, $to - $pointer);

        // var parsed, check if any var-extension applies
        if ($parsed === 'var') {
            if (preg_match('#^\s*([/%+*-])\s*([a-z0-9]|\$)#i', $substr, $match)) {
                if ($this->debug) {
                    echo 'PARSING POST-VAR EXPRESSION ' . $substr . "\n";
                }
                // parse expressions
                $pointer += strlen($match[0]) - 1;
                if (is_array($parsingParams)) {
                    if ($match[2] == '$') {
                        $expr = $this->parseVar($in, $pointer, $to, array(), $curBlock, $pointer);
                    } else {
                        $expr = $this->parse($in, $pointer, $to, array(), 'expression', $pointer);
                    }
                    $out[count($out) - 1][0] .= $match[1] . $expr[0][0];
                    $out[count($out) - 1][1] .= $match[1] . $expr[0][1];
                } else {
                    if ($match[2] == '$') {
                        $expr = $this->parseVar($in, $pointer, $to, false, $curBlock, $pointer);
                    } else {
                        $expr = $this->parse($in, $pointer, $to, false, 'expression', $pointer);
                    }
                    if (is_array($out) && is_array($expr)) {
                        $out[0] .= $match[1] . $expr[0];
                        $out[1] .= $match[1] . $expr[1];
                    } elseif (is_array($out)) {
                        $out[0] .= $match[1] . $expr;
                        $out[1] .= $match[1] . $expr;
                    } elseif (is_array($expr)) {
                        $out .= $match[1] . $expr[0];
                    } else {
                        $out .= $match[1] . $expr;
                    }
                }
            } elseif ($curBlock === 'root' && preg_match('#^(\s*(?:[+/*%-.]=|=|\+\+|--)\s*)(.*)#s', $substr, $match)) {
                if ($this->debug) {
                    echo 'PARSING POST-VAR ASSIGNMENT ' . $substr . "\n";
                }
                // parse assignment
                $value    = $match[2];
                $operator = trim($match[1]);
                if (substr($value, 0, 1) == '=') {
                    throw new CompilationException($this, 'Unexpected "=" in <em>' . $substr . '</em>');
                }

                if ($pointer !== null) {
                    $pointer += strlen($match[1]);
                }

                if ($operator !== '++' && $operator !== '--') {
                    $parts = array();
                    $ptr   = 0;
                    $parts = $this->parse($value, 0, strlen($value), $parts, 'condition', $ptr);
                    $pointer += $ptr;

                    // load if plugin
                    try {
                        $this->getPluginType('if');
                    }
                    catch (Exception $e) {
                        throw new CompilationException($this, 'Assignments require the "if" plugin to be accessible');
                    }

                    $parts  = $this->mapParams($parts, array(Core::NAMESPACE_PLUGINS_BLOCKS . 'PluginIf', 'init'), 1);
                    $tokens = $this->getParamTokens($parts);
                    $parts  = $this->getCompiledParams($parts);

                    $value = PluginIf::replaceKeywords($parts['*'], $tokens['*'], $this);
                    $echo  = '';
                } else {
                    $value = array();
                    $echo  = 'echo ';
                }

                if ($this->autoEscape) {
                    $out = preg_replace('#\(is_string\(\$tmp=(.+?)\) \? htmlspecialchars\(\$tmp, ENT_QUOTES, \$this->charset\) : \$tmp\)#', '$1', $out);
                }
                $out = self::PHP_OPEN . $echo . $out . $operator . implode(' ', $value) . self::PHP_CLOSE;
            } elseif ($curBlock === 'array' && is_array($parsingParams) && preg_match('#^(\s*=>?\s*)#', $substr, $match)) {
                // parse namedparam with var as name (only for array)
                if ($this->debug) {
                    echo 'VARIABLE NAMED PARAM (FOR ARRAY) FOUND' . "\n";
                }
                $len = strlen($match[1]);
                $var = $out[count($out) - 1];
                $pointer += $len;

                $output = array($var[0], $this->parse($substr, $len, null, false, 'namedparam', $pointer));

                $parsingParams[] = $output;

                return $parsingParams;
            }
        }

        if ($curBlock !== 'modifier' && ($parsed === 'func' || $parsed === 'var') && preg_match('#^(\|@?[a-z0-9_]+(:.*)?)+#i', $substr, $match)) {
            // parse modifier on funcs or vars
            $srcPointer = $pointer;
            if (is_array($parsingParams)) {
                $tmp                     = $this->replaceModifiers(
                    array(
                    null,
                    null,
                    $out[count($out) - 1][0],
                    $match[0]
                    ), $curBlock, $pointer
                );
                $out[count($out) - 1][0] = $tmp;
                $out[count($out) - 1][1] .= substr($substr, $srcPointer, $srcPointer - $pointer);
            } else {
                $out = $this->replaceModifiers(array(null, null, $out, $match[0]), $curBlock, $pointer);
            }
        }

        // func parsed, check if any func-extension applies
        if ($parsed === 'func' && preg_match('#^->[a-z0-9_]+(\s*\(.+|->[a-z_].*)?#is', $substr, $match)) {
            // parse method call or property read
            $ptr = 0;

            if (is_array($parsingParams)) {
                $output = $this->parseMethodCall($out[count($out) - 1][1], $match[0], $curBlock, $ptr);

                $out[count($out) - 1][0] = $output;
                $out[count($out) - 1][1] .= substr($match[0], 0, $ptr);
            } else {
                $out = $this->parseMethodCall($out, $match[0], $curBlock, $ptr);
            }

            $pointer += $ptr;
        }

        if ($curBlock === 'root' && substr($out, 0, strlen(self::PHP_OPEN)) !== self::PHP_OPEN) {
            return self::PHP_OPEN . 'echo ' . $out . ';' . self::PHP_CLOSE;
        }

        return $out;
    }

    /**
     * Parses a function call.
     *
     * @param string $in            the string within which we must parse something
     * @param int    $from          the starting offset of the parsed area
     * @param int    $to            the ending offset of the parsed area
     * @param mixed  $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by
     *                              default
     * @param string $curBlock      the current parser-block being processed
     * @param mixed  $pointer       a reference to a pointer that will be increased by the amount of characters parsed,
     *                              or null by default
     *
     * @return string parsed values
     * @throws CompilationException
     * @throws Exception
     * @throws SecurityException
     */
    protected function parseFunction($in, $from, $to, $parsingParams = false, $curBlock = '', &$pointer = null)
    {
        $output = '';
        $cmdstr = substr($in, $from, $to - $from);
        preg_match('/^(\\\\?[a-z_](?:\\\\?[a-z0-9_]+)*(?:::[a-z_][a-z0-9_]*)?)(\s*' . $this->rdr . '|\s*;)?/i', $cmdstr, $match);

        if (empty($match[1])) {
            throw new CompilationException($this, 'Parse error, invalid function name : ' . substr($cmdstr, 0, 15));
        }

        $func = $match[1];

        if (!empty($match[2])) {
            $cmdstr = $match[1];
        }

        if ($this->debug) {
            echo 'FUNC FOUND (' . $func . ')' . "\n";
        }

        $paramsep = '';

        if (is_array($parsingParams) || $curBlock != 'root') {
            $paramspos = strpos($cmdstr, '(');
            $paramsep  = ')';
        } elseif (preg_match_all('#^\s*[\\\\:a-z0-9_]+(\s*\(|\s+[^(])#i', $cmdstr, $match, PREG_OFFSET_CAPTURE)) {
            $paramspos = $match[1][0][1];
            $paramsep  = substr($match[1][0][0], - 1) === '(' ? ')' : '';
            if ($paramsep === ')') {
                $paramspos += strlen($match[1][0][0]) - 1;
                if (substr($cmdstr, 0, 2) === 'if' || substr($cmdstr, 0, 6) === 'elseif') {
                    $paramsep = '';
                    if (strlen($match[1][0][0]) > 1) {
                        -- $paramspos;
                    }
                }
            }
        } else {
            $paramspos = false;
        }

        $state = 0;

        if ($paramspos === false) {
            $params = array();

            if ($curBlock !== 'root') {
                return $this->parseOthers($in, $from, $to, $parsingParams, $curBlock, $pointer);
            }
        } else {
            if ($curBlock === 'condition') {
                // load if plugin
                $this->getPluginType('if');

                if (PluginIf::replaceKeywords(array($func), array(self::T_UNQUOTED_STRING), $this) !== array($func)) {
                    return $this->parseOthers($in, $from, $to, $parsingParams, $curBlock, $pointer);
                }
            }
            $whitespace = strlen(substr($cmdstr, strlen($func), $paramspos - strlen($func)));
            $paramstr   = substr($cmdstr, $paramspos + 1);
            if (substr($paramstr, - 1, 1) === $paramsep) {
                $paramstr = substr($paramstr, 0, - 1);
            }

            if (strlen($paramstr) === 0) {
                $params   = array();
                $paramstr = '';
            } else {
                $ptr    = 0;
                $params = array();
                if ($func === 'empty') {
                    $params = $this->parseVar($paramstr, $ptr, strlen($paramstr), $params, 'root', $ptr);
                } else {
                    while ($ptr < strlen($paramstr)) {
                        while (true) {
                            if ($ptr >= strlen($paramstr)) {
                                break 2;
                            }

                            if ($func !== 'if' && $func !== 'elseif' && $paramstr[$ptr] === ')') {
                                if ($this->debug) {
                                    echo 'PARAM PARSING ENDED, ")" FOUND, POINTER AT ' . $ptr . "\n";
                                }
                                break 2;
                            } elseif ($paramstr[$ptr] === ';') {
                                ++ $ptr;
                                if ($this->debug) {
                                    echo 'PARAM PARSING ENDED, ";" FOUND, POINTER AT ' . $ptr . "\n";
                                }
                                break 2;
                            } elseif ($func !== 'if' && $func !== 'elseif' && $paramstr[$ptr] === '/') {
                                if ($this->debug) {
                                    echo 'PARAM PARSING ENDED, "/" FOUND, POINTER AT ' . $ptr . "\n";
                                }
                                break 2;
                            } elseif (substr($paramstr, $ptr, strlen($this->rd)) === $this->rd) {
                                if ($this->debug) {
                                    echo 'PARAM PARSING ENDED, RIGHT DELIMITER FOUND, POINTER AT ' . $ptr . "\n";
                                }
                                break 2;
                            }

                            if ($paramstr[$ptr] === ' ' || $paramstr[$ptr] === ',' || $paramstr[$ptr] === "\r" || $paramstr[$ptr] === "\n" || $paramstr[$ptr] === "\t") {
                                ++ $ptr;
                            } else {
                                break;
                            }
                        }

                        if ($this->debug) {
                            echo 'FUNC START PARAM PARSING WITH POINTER AT ' . $ptr . "\n";
                        }

                        if ($func === 'if' || $func === 'elseif' || $func === 'tif') {
                            $params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'condition', $ptr);
                        } elseif ($func === 'array') {
                            $params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'array', $ptr);
                        } else {
                            $params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'function', $ptr);
                        }

                        if ($this->debug) {
                            echo 'PARAM PARSED, POINTER AT ' . $ptr . ' (' . substr($paramstr, $ptr - 1, 3) . ')' . "\n";
                        }
                    }
                }
                $paramstr = substr($paramstr, 0, $ptr);
                $state    = 0;
                foreach ($params as $k => $p) {
                    if (is_array($p) && is_array($p[1])) {
                        $state |= 2;
                    } else {
                        if (($state & 2) && preg_match('#^(["\'])(.+?)\1$#', $p[0], $m) && $func !== 'array') {
                            $params[$k] = array($m[2], array('true', 'true'));
                        } else {
                            if ($state & 2 && $func !== 'array') {
                                throw new CompilationException($this, 'You can not use an unnamed parameter after a named one');
                            }
                            $state |= 1;
                        }
                    }
                }
            }
        }

        if ($pointer !== null) {
            $pointer += (isset($paramstr) ? strlen($paramstr) : 0) + (')' === $paramsep ? 2 : ($paramspos === false ? 0 : 1)) + strlen($func) + (isset($whitespace) ? $whitespace : 0);
            if ($this->debug) {
                echo 'FUNC ADDS ' . ((isset($paramstr) ? strlen($paramstr) : 0) + (')' === $paramsep ? 2 : ($paramspos === false ? 0 : 1)) + strlen($func)) . ' TO POINTER' . "\n";
            }
        }

        if ($curBlock === 'method' || $func === 'do' || strstr($func, '::') !== false) {
            // handle static method calls with security policy
            if (strstr($func, '::') !== false && $this->securityPolicy !== null && $this->securityPolicy->isMethodAllowed(explode('::', strtolower($func))) !== true) {
                throw new SecurityException('Call to a disallowed php function : ' . $func);
            }
            $pluginType = Core::NATIVE_PLUGIN;
        } else {
            $pluginType = $this->getPluginType($func);
        }

        // Blocks plugin
        if ($pluginType & Core::BLOCK_PLUGIN) {
            if ($curBlock !== 'root' || is_array($parsingParams)) {
                throw new CompilationException($this, 'Block plugins can not be used as other plugin\'s arguments');
            }
            if ($pluginType & Core::CUSTOM_PLUGIN) {
                return $this->addCustomBlock($func, $params, $state);
            } else {
                return $this->addBlock($func, $params, $state);
            }
        } elseif ($pluginType & Core::SMARTY_BLOCK) {
            if ($curBlock !== 'root' || is_array($parsingParams)) {
                throw new CompilationException($this, 'Block plugins can not be used as other plugin\'s arguments');
            }

            if ($state & 2) {
                array_unshift($params, array('__functype', array($pluginType, $pluginType)));
                array_unshift($params, array('__funcname', array($func, $func)));
            } else {
                array_unshift($params, array($pluginType, $pluginType));
                array_unshift($params, array($func, $func));
            }

            return $this->addBlock('smartyinterface', $params, $state);
        }

        // Native & Smarty plugins
        if ($pluginType & Core::NATIVE_PLUGIN || $pluginType & Core::SMARTY_FUNCTION || $pluginType & Core::SMARTY_BLOCK) {
            $params = $this->mapParams($params, null, $state);
        } // PHP class plugin
        elseif ($pluginType & Core::CLASS_PLUGIN) {
            if ($pluginType & Core::CUSTOM_PLUGIN) {
                $params = $this->mapParams(
                    $params, array(
                    $this->customPlugins[$func]['class'],
                    $this->customPlugins[$func]['function']
                ), $state);
            } else {
                if (class_exists('Plugin' . Core::toCamelCase($func)) !== false) {
                    $params = $this->mapParams($params, array(
                        'Plugin' . Core::toCamelCase($func),
                        ($pluginType & Core::COMPILABLE_PLUGIN) ? 'compile' : 'process'
                    ), $state);
                } elseif (class_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func)) !== false) {
                    $params = $this->mapParams($params, array(
                        Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func),
                        ($pluginType & Core::COMPILABLE_PLUGIN) ? 'compile' : 'process'
                    ), $state);
                } else {
                    $params = $this->mapParams($params, array(
                        Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func),
                        ($pluginType & Core::COMPILABLE_PLUGIN) ? 'compile' : 'process'
                    ), $state);
                }
            }
        } // PHP function plugin
        elseif ($pluginType & Core::FUNC_PLUGIN) {
            if ($pluginType & Core::CUSTOM_PLUGIN) {
                $params = $this->mapParams($params, $this->customPlugins[$func]['callback'], $state);
            } else {
                // Custom plugin
                if (function_exists('Plugin' . Core::toCamelCase($func) . (($pluginType & Core::COMPILABLE_PLUGIN) ?
                        'Compile' : '')) !== false) {
                    $params = $this->mapParams($params, 'Plugin' . Core::toCamelCase($func) . (($pluginType &
                            Core::COMPILABLE_PLUGIN) ? 'Compile' : ''), $state);
                } // Builtin helper plugin
                elseif (function_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func) . (
                    ($pluginType & Core::COMPILABLE_PLUGIN) ? 'Compile' : '')) !== false) {
                    $params = $this->mapParams($params, Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase
                        ($func) . (($pluginType & Core::COMPILABLE_PLUGIN) ? 'Compile' : ''), $state);
                } // Builtin function plugin
                else {
                    $params = $this->mapParams($params, Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase
                        ($func) . (($pluginType & Core::COMPILABLE_PLUGIN) ? 'Compile' : ''), $state);
                }
            }
        } // Smarty modifier
        elseif ($pluginType & Core::SMARTY_MODIFIER) {
            $output = 'smarty_modifier_' . $func . '(' . implode(', ', $params) . ')';
        } // Proxy plugin
        elseif ($pluginType & Core::PROXY_PLUGIN) {
            $params = $this->mapParams($params, $this->getCore()->getPluginProxy()->getCallback($func), $state);
        } // Template plugin
        elseif ($pluginType & Core::TEMPLATE_PLUGIN) {
            // transforms the parameter array from (x=>array('paramname'=>array(values))) to (paramname=>array(values))
            $map = array();
            foreach ($this->templatePlugins[$func]['params'] as $param => $defValue) {
                if ($param == 'rest') {
                    $param = '*';
                }
                $hasDefault = $defValue !== null;
                if ($defValue === 'null') {
                    $defValue = null;
                } elseif ($defValue === 'false') {
                    $defValue = false;
                } elseif ($defValue === 'true') {
                    $defValue = true;
                } elseif (preg_match('#^([\'"]).*?\1$#', $defValue)) {
                    $defValue = substr($defValue, 1, - 1);
                }
                $map[] = array($param, $hasDefault, $defValue);
            }

            $params = $this->mapParams($params, null, $state, $map);
        }

        // only keep php-syntax-safe values for non-block plugins
        $tokens = array();
        foreach ($params as $k => $p) {
            $tokens[$k] = isset($p[2]) ? $p[2] : 0;
            $params[$k] = $p[0];
        }

        // Native plugin
        if ($pluginType & Core::NATIVE_PLUGIN) {
            if ($func === 'do') {
                $output = '';
                if (isset($params['*'])) {
                    $output = implode(';', $params['*']) . ';';
                }

                if (is_array($parsingParams) || $curBlock !== 'root') {
                    throw new CompilationException($this, 'Do can not be used inside another function or block');
                }

                return self::PHP_OPEN . $output . self::PHP_CLOSE;
            } else {
                if (isset($params['*'])) {
                    $output = $func . '(' . implode(', ', $params['*']) . ')';
                } else {
                    $output = $func . '()';
                }
            }
        } // Block class OR Function class
        elseif ($pluginType & Core::CLASS_PLUGIN || ($pluginType & Core::FUNC_PLUGIN && $pluginType & Core::CLASS_PLUGIN)) {
            if ($pluginType & Core::COMPILABLE_PLUGIN) {
                if ($pluginType & Core::CUSTOM_PLUGIN) {
                    $callback = $this->customPlugins[$func]['callback'];
                    if (!is_array($callback)) {
                        if (!method_exists($callback, 'compile')) {
                            throw new Exception('Custom plugin ' . $func . ' must implement the "compile" method to be compilable, or you should provide a full callback to the method to use');
                        }
                        if (($ref = new ReflectionMethod($callback, 'compile')) && $ref->isStatic()) {
                            $funcCompiler = array($callback, 'compile');
                        } else {
                            $funcCompiler = array(new $callback(), 'compile');
                        }
                    } else {
                        $funcCompiler = $callback;
                    }
                } else {
                    if (class_exists('Plugin' . Core::toCamelCase($func)) !== false) {
                        $funcCompiler = array('Plugin' . Core::toCamelCase($func), 'compile');
                    } elseif (class_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func)) !== false) {
                        $funcCompiler = array(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func), 'compile');
                    } else {
                        $funcCompiler = array(
                            Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func),
                            'compile'
                        );
                    }
                    array_unshift($params, $this);
                }
                // @TODO: Is it a real fix ?
                if ($func === 'tif') {
                    $params[] = $tokens;
                }
                $output = call_user_func_array($funcCompiler, $params);
            } else {
                $params = self::implode_r($params);
                if ($pluginType & Core::CUSTOM_PLUGIN) {
                    $callback = $this->customPlugins[$func]['callback'];
                    if (!is_array($callback)) {
                        if (!method_exists($callback, 'process')) {
                            throw new Exception('Custom plugin ' . $func . ' must implement the "process" method to be usable, or you should provide a full callback to the method to use');
                        }
                        if (is_object($callback)) {
                            $callback = get_class($callback);
                        }
                        if (($ref = new ReflectionMethod($callback, 'process')) && $ref->isStatic()) {
                            $output = 'call_user_func(array(\'' . $callback . '\', \'process\'), ' . $params . ')';
                        } else {
                            $output = 'call_user_func(array($this->getObjectPlugin(\'' . $callback . '\'), \'process\'), ' . $params . ')';
                        }
                    } elseif (is_object($callback[0])) {
                        $output = 'call_user_func(array($this->plugins[\'' . $func . '\'][\'callback\'][0], \'' . $callback[1] . '\'), ' . $params . ')';
                    } elseif (($ref = new ReflectionMethod($callback[0], $callback[1])) && $ref->isStatic()) {
                        $output = 'call_user_func(array(\'' . $callback[0] . '\', \'' . $callback[1] . '\'), ' . $params . ')';
                    } else {
                        $output = 'call_user_func(array($this->getObjectPlugin(\'' . $callback[0] . '\'), \'' . $callback[1] . '\'), ' . $params . ')';
                    }
                    if (empty($params)) {
                        $output = substr($output, 0, - 3) . ')';
                    }
                } else {
                    if (class_exists('Plugin' . Core::toCamelCase($func)) !== false) {
                        $output = '$this->classCall(\'Plugin' . $func . '\', array(' . $params . '))';
                    } elseif (class_exists(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func)) !== false) {
                        $output = '$this->classCall(\'' . Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . $func . '\', 
                        array(' . $params . '))';
                    } else {
                        $output = '$this->classCall(\'' . $func . '\', array(' . $params . '))';
                    }
                }
            }
        } // Function plugin only (cannot be a class)
        elseif ($pluginType & Core::FUNC_PLUGIN) {
            if ($pluginType & Core::COMPILABLE_PLUGIN) {
                if ($pluginType & Core::CUSTOM_PLUGIN) {
                    $funcCompiler = $this->customPlugins[$func]['callback'];
                } else {
                    // Custom plugin
                    if (function_exists('Plugin' . Core::toCamelCase($func) . 'Compile') !== false) {
                        $funcCompiler = 'Plugin' . Core::toCamelCase($func) . 'Compile';
                    } // Builtin helper plugin
                    elseif (function_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func) . 'Compile') !== false) {
                        $funcCompiler = Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func) .
                            'Compile';
                    } // Builtin function plugin
                    else {
                        $funcCompiler = Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func) .
                            'Compile';
                    }
                }
                array_unshift($params, $this);
                // @TODO: Is it a real fix ?
                if ($func === 'tif') {
                    $params[] = $tokens;
                }
                $output = call_user_func_array($funcCompiler, $params);
            } else {
                array_unshift($params, '$this');
                $params = self::implode_r($params);
                if ($pluginType & Core::CUSTOM_PLUGIN) {
                    $callback = $this->customPlugins[$func]['callback'];
                    if ($callback instanceof Closure) {
                        $output = 'call_user_func($this->getCustomPlugin(\'' . $func . '\'), ' . $params . ')';
                    } else {
                        $output = 'call_user_func(\'' . $callback . '\', ' . $params . ')';
                    }
                } else {
                    // Custom plugin
                    if (function_exists('Plugin' . Core::toCamelCase($func)) !== false) {
                        $output = 'Plugin' . Core::toCamelCase($func) . '(' . $params .
                            ')';
                    } // Builtin helper plugin
                    elseif(function_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func)) !==
                        false) {
                        $output = Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($func) . '(' .
                            $params . ')';
                    } // Builtin function plugin
                    else {
                        $output = Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func) . '(' .
                            $params . ')';
                    }
                }
            }
        } // Proxy plugin
        elseif ($pluginType & Core::PROXY_PLUGIN) {
            $output = call_user_func(array($this->getCore()->getPluginProxy(), 'getCode'), $func, $params);
        } // Smarty function (@deprecated)
        elseif ($pluginType & Core::SMARTY_FUNCTION) {
            $params = '';
            if (isset($params['*'])) {
                $params = self::implode_r($params['*'], true);
            }

            if ($pluginType & Core::CUSTOM_PLUGIN) {
                $callback = $this->customPlugins[$func]['callback'];
                if (is_array($callback)) {
                    if (is_object($callback[0])) {
                        $output = 'call_user_func_array(array($this->getCustomPlugin(\'' . $func . '\'), \'' . $callback[1] . '\'), array(array(' . $params . '), $this))';
                    } else {
                        $output = 'call_user_func_array(array(\'' . $callback[0] . '\', \'' . $callback[1] . '\'), array(array(' . $params . '), $this))';
                    }
                } else {
                    $output = $callback . '(array(' . $params . '), $this)';
                }
            } else {
                $output = 'smarty_function_' . $func . '(array(' . $params . '), $this)';
            }
        } // Template plugin
        elseif ($pluginType & Core::TEMPLATE_PLUGIN) {
            array_unshift($params, '$this');
            $params                                 = self::implode_r($params);
            $output                                 = 'Plugin' . Core::toCamelCase($func) .
                $this->templatePlugins[$func]['uuid'] . '(' . $params . ')';
            $this->templatePlugins[$func]['called'] = true;
        }

        if (is_array($parsingParams)) {
            $parsingParams[] = array($output, $output);

            return $parsingParams;
        } elseif ($curBlock === 'namedparam') {
            return array($output, $output);
        }

        return $output;
    }

    /**
     * Parses a string.
     *
     * @param string $in            the string within which we must parse something
     * @param int    $from          the starting offset of the parsed area
     * @param int    $to            the ending offset of the parsed area
     * @param mixed  $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by
     *                              default
     * @param string $curBlock      the current parser-block being processed
     * @param mixed  $pointer       a reference to a pointer that will be increased by the amount of characters parsed,
     *                              or null by default
     *
     * @return string parsed values
     * @throws CompilationException
     */
    protected function parseString($in, $from, $to, $parsingParams = false, $curBlock = '', &$pointer = null)
    {
        $substr = substr($in, $from, $to - $from);
        $first  = $substr[0];

        if ($this->debug) {
            echo 'STRING FOUND (in ' . htmlentities(substr($in, $from, min($to - $from, 50))) . (($to - $from) > 50 ? '...' : '') . ')' . "\n";
        }
        $strend = false;
        $o      = $from + 1;
        while ($strend === false) {
            $strend = strpos($in, $first, $o);
            if ($strend === false) {
                throw new CompilationException($this, 'Unfinished string, started with ' . substr($in, $from, $to - $from));
            }
            if (substr($in, $strend - 1, 1) === '\\') {
                $o      = $strend + 1;
                $strend = false;
            }
        }
        if ($this->debug) {
            echo 'STRING DELIMITED: ' . substr($in, $from, $strend + 1 - $from) . "\n";
        }

        $srcOutput = substr($in, $from, $strend + 1 - $from);

        if ($pointer !== null) {
            $pointer += strlen($srcOutput);
        }

        $output = $this->replaceStringVars($srcOutput, $first);

        // handle modifiers
        if ($curBlock !== 'modifier' && preg_match('#^((?:\|(?:@?[a-z0-9_]+(?::.*)*))+)#i', substr($substr, $strend + 1 - $from), $match)) {
            $modstr = $match[1];

            if ($curBlock === 'root' && substr($modstr, - 1) === '}') {
                $modstr = substr($modstr, 0, - 1);
            }
            $modstr = str_replace('\\' . $first, $first, $modstr);
            $ptr    = 0;
            $output = $this->replaceModifiers(array(null, null, $output, $modstr), 'string', $ptr);

            $strend += $ptr;
            if ($pointer !== null) {
                $pointer += $ptr;
            }
            $srcOutput .= substr($substr, $strend + 1 - $from, $ptr);
        }

        if (is_array($parsingParams)) {
            $parsingParams[] = array($output, substr($srcOutput, 1, - 1));

            return $parsingParams;
        } elseif ($curBlock === 'namedparam') {
            return array($output, substr($srcOutput, 1, - 1));
        }

        return $output;
    }

    /**
     * Parses a constant.
     *
     * @param string $in            the string within which we must parse something
     * @param int    $from          the starting offset of the parsed area
     * @param int    $to            the ending offset of the parsed area
     * @param mixed  $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by
     *                              default
     * @param string $curBlock      the current parser-block being processed
     * @param mixed  $pointer       a reference to a pointer that will be increased by the amount of characters parsed,
     *                              or null by default
     *
     * @return string parsed values
     * @throws CompilationException
     */
    protected function parseConst($in, $from, $to, $parsingParams = false, $curBlock = '', &$pointer = null)
    {
        $substr = substr($in, $from, $to - $from);

        if ($this->debug) {
            echo 'CONST FOUND : ' . $substr . "\n";
        }

        if (!preg_match('#^%([\\\\a-z0-9_:]+)#i', $substr, $m)) {
            throw new CompilationException($this, 'Invalid constant');
        }

        if ($pointer !== null) {
            $pointer += strlen($m[0]);
        }

        $output = $this->parseConstKey($m[1], $curBlock);

        if (is_array($parsingParams)) {
            $parsingParams[] = array($output, $m[1]);

            return $parsingParams;
        } elseif ($curBlock === 'namedparam') {
            return array($output, $m[1]);
        }

        return $output;
    }

    /**
     * Parses a constant.
     *
     * @param string $key      the constant to parse
     * @param string $curBlock the current parser-block being processed
     *
     * @return string parsed constant
     */
    protected function parseConstKey($key, $curBlock)
    {
        $key = str_replace('\\\\', '\\', $key);

        if ($this->securityPolicy !== null && $this->securityPolicy->getConstantHandling() === SecurityPolicy::CONST_DISALLOW) {
            return 'null';
        }

        if ($curBlock !== 'root') {
            return '(defined("' . $key . '") ? ' . $key . ' : null)';
        }

        return $key;
    }

    /**
     * Parses a variable.
     *
     * @param string $in            the string within which we must parse something
     * @param int    $from          the starting offset of the parsed area
     * @param int    $to            the ending offset of the parsed area
     * @param mixed  $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by
     *                              default
     * @param string $curBlock      the current parser-block being processed
     * @param mixed  $pointer       a reference to a pointer that will be increased by the amount of characters parsed,
     *                              or null by default
     *
     * @return string parsed values
     * @throws CompilationException
     */
    protected function parseVar($in, $from, $to, $parsingParams = false, $curBlock = '', &$pointer = null)
    {
        $substr = substr($in, $from, $to - $from);

        // var key
        $varRegex = '(\\$?\\.?[a-z0-9\\\\_:]*(?:(?:(?:\\.|->)(?:[a-z0-9\\\\_:]+|(?R))|\\[(?:[a-z0-9\\\\_:]+|(?R)|(["\'])[^\\2]*?\\2)\\]))*)';
        // method call
        $methodCall = ($curBlock === 'root' || $curBlock === 'function' || $curBlock === 'namedparam' || $curBlock === 'condition' || $curBlock === 'variable' || $curBlock === 'expression' || $curBlock === 'delimited_string' ? '(\(.*)?' : '()');
        // simple math expressions
        $simpleMathExpressions = ($curBlock === 'root' || $curBlock === 'function' || $curBlock === 'namedparam' || $curBlock === 'condition' || $curBlock === 'variable' || $curBlock === 'delimited_string' ? '((?:(?:[+\/*%=-])(?:(?<!=)=?-?[$%][a-z0-9\\\\.[\]>_:-]+(?:\([^)]*\))?|(?<!=)=?-?[0-9\.,]*|[+-]))*)' : '()');
        // modifiers
        $modifiers = $curBlock !== 'modifier' ? '((?:\|(?:@?[a-z0-9\\\\_]+(?:(?::("|\').*?\5|:[^`]*))*))+)?' : '(())';

        $regex = '#';
        $regex .= $varRegex;
        $regex .= $methodCall;
        $regex .= $simpleMathExpressions;
        $regex .= $modifiers;
        $regex .= '#i';

        if (preg_match($regex, $substr, $match)) {
            $key = substr($match[1], 1);

            $matchedLength = strlen($match[0]);
            $hasModifiers  = !empty($match[5]);
            $hasExpression = !empty($match[4]);
            $hasMethodCall = !empty($match[3]);

            if (substr($key, - 1) == '.') {
                $key = substr($key, 0, - 1);
                -- $matchedLength;
            }

            if ($hasMethodCall) {
                $matchedLength -= strlen($match[3]) + strlen(substr($match[1], strrpos($match[1], '->')));
                $key        = substr($match[1], 1, strrpos($match[1], '->') - 1);
                $methodCall = substr($match[1], strrpos($match[1], '->')) . $match[3];
            }

            if ($hasModifiers) {
                $matchedLength -= strlen($match[5]);
            }

            if ($pointer !== null) {
                $pointer += $matchedLength;
            }

            // replace useless brackets by dot accessed vars and strip enclosing quotes if present
            $key = preg_replace('#\[(["\']?)([^$%\[.>-]+)\1\]#', '.$2', $key);

            if ($this->debug) {
                if ($hasMethodCall) {
                    echo 'METHOD CALL FOUND : $' . $key . substr($methodCall, 0, 30) . "\n";
                } else {
                    echo 'VAR FOUND : $' . $key . "\n";
                }
            }

            $key = str_replace('"', '\\"', $key);

            $cnt = substr_count($key, '$');
            if ($cnt > 0) {
                $uid           = 0;
                $parsed        = array($uid => '');
                $current       = &$parsed;
                $curTxt        = &$parsed[$uid ++];
                $tree          = array();
                $chars         = str_split($key, 1);
                $inSplittedVar = false;
                $bracketCount  = 0;

                while (($char = array_shift($chars)) !== null) {
                    if ($char === '[') {
                        if (count($tree) > 0) {
                            ++ $bracketCount;
                        } else {
                            $tree[]        = &$current;
                            $current[$uid] = array($uid + 1 => '');
                            $current       = &$current[$uid ++];
                            $curTxt        = &$current[$uid ++];
                            continue;
                        }
                    } elseif ($char === ']') {
                        if ($bracketCount > 0) {
                            -- $bracketCount;
                        } else {
                            $current = &$tree[count($tree) - 1];
                            array_pop($tree);
                            if (current($chars) !== '[' && current($chars) !== false && current($chars) !== ']') {
                                $current[$uid] = '';
                                $curTxt        = &$current[$uid ++];
                            }
                            continue;
                        }
                    } elseif ($char === '$') {
                        if (count($tree) == 0) {
                            $curTxt        = &$current[$uid ++];
                            $inSplittedVar = true;
                        }
                    } elseif (($char === '.' || $char === '-') && count($tree) == 0 && $inSplittedVar) {
                        $curTxt        = &$current[$uid ++];
                        $inSplittedVar = false;
                    }

                    $curTxt .= $char;
                }
                unset($uid, $current, $curTxt, $tree, $chars);

                if ($this->debug) {
                    echo 'RECURSIVE VAR REPLACEMENT : ' . $key . "\n";
                }

                $key = $this->flattenVarTree($parsed);

                if ($this->debug) {
                    echo 'RECURSIVE VAR REPLACEMENT DONE : ' . $key . "\n";
                }

                $output = preg_replace('#(^""\.|""\.|\.""$|(\()""\.|\.""(\)))#', '$2$3', '$this->readVar("' . $key . '")');
            } else {
                $output = $this->parseVarKey($key, $hasModifiers ? 'modifier' : $curBlock);
            }


            // methods
            if ($hasMethodCall) {
                $ptr = 0;

                $output = $this->parseMethodCall($output, $methodCall, $curBlock, $ptr);

                if ($pointer !== null) {
                    $pointer += $ptr;
                }
                $matchedLength += $ptr;
            }

            if ($hasExpression) {
                // expressions
                preg_match_all('#(?:([+/*%=-])(=?-?[%$][a-z0-9\\\\.[\]>_:-]+(?:\([^)]*\))?|=?-?[0-9.,]+|\1))#i', $match[4], $expMatch);
                foreach ($expMatch[1] as $k => $operator) {
                    if (substr($expMatch[2][$k], 0, 1) === '=') {
                        $assign = true;
                        if ($operator === '=') {
                            throw new CompilationException($this, 'Invalid expression <em>' . $substr . '</em>, can not use "==" in expressions');
                        }
                        if ($curBlock !== 'root') {
                            throw new CompilationException($this, 'Invalid expression <em>' . $substr . '</em>, assignments can only be used in top level expressions like {$foo+=3} or {$foo="bar"}');
                        }
                        $operator .= '=';
                        $expMatch[2][$k] = substr($expMatch[2][$k], 1);
                    }

                    if (substr($expMatch[2][$k], 0, 1) === '-' && strlen($expMatch[2][$k]) > 1) {
                        $operator .= '-';
                        $expMatch[2][$k] = substr($expMatch[2][$k], 1);
                    }
                    if (($operator === '+' || $operator === '-') && $expMatch[2][$k] === $operator) {
                        $output = '(' . $output . $operator . $operator . ')';
                        break;
                    } elseif (substr($expMatch[2][$k], 0, 1) === '$') {
                        $output = '(' . $output . ' ' . $operator . ' ' . $this->parseVar($expMatch[2][$k], 0, strlen($expMatch[2][$k]), false, 'expression') . ')';
                    } elseif (substr($expMatch[2][$k], 0, 1) === '%') {
                        $output = '(' . $output . ' ' . $operator . ' ' . $this->parseConst($expMatch[2][$k], 0, strlen($expMatch[2][$k]), false, 'expression') . ')';
                    } elseif (!empty($expMatch[2][$k])) {
                        $output = '(' . $output . ' ' . $operator . ' ' . str_replace(',', '.', $expMatch[2][$k]) . ')';
                    } else {
                        throw new CompilationException($this, 'Unfinished expression <em>' . $substr . '</em>, missing var or number after math operator');
                    }
                }
            }

            if ($this->autoEscape === true && $curBlock !== 'condition') {
                $output = '(is_string($tmp=' . $output . ') ? htmlspecialchars($tmp, ENT_QUOTES, $this->charset) : $tmp)';
            }

            // handle modifiers
            if ($curBlock !== 'modifier' && $hasModifiers) {
                $ptr    = 0;
                $output = $this->replaceModifiers(array(null, null, $output, $match[5]), 'var', $ptr);
                if ($pointer !== null) {
                    $pointer += $ptr;
                }
                $matchedLength += $ptr;
            }

            if (is_array($parsingParams)) {
                $parsingParams[] = array($output, $key);

                return $parsingParams;
            } elseif ($curBlock === 'namedparam') {
                return array($output, $key);
            } elseif ($curBlock === 'string' || $curBlock === 'delimited_string') {
                return array($matchedLength, $output);
            } elseif ($curBlock === 'expression' || $curBlock === 'variable') {
                return $output;
            } elseif (isset($assign)) {
                return self::PHP_OPEN . $output . ';' . self::PHP_CLOSE;
            }

            return $output;
        } else {
            if ($curBlock === 'string' || $curBlock === 'delimited_string') {
                return array(0, '');
            }
            throw new CompilationException($this, 'Invalid variable name <em>' . $substr . '</em>');
        }
    }

    /**
     * Parses any number of chained method calls/property reads.
     *
     * @param string $output     the variable or whatever upon which the method are called
     * @param string $methodCall method call source, starting at "->"
     * @param string $curBlock   the current parser-block being processed
     * @param int    $pointer    a reference to a pointer that will be increased by the amount of characters parsed
     *
     * @return string parsed call(s)/read(s)
     */
    protected function parseMethodCall($output, $methodCall, $curBlock, &$pointer)
    {
        $ptr = 0;
        $len = strlen($methodCall);

        while ($ptr < $len) {
            if (strpos($methodCall, '->', $ptr) === $ptr) {
                $ptr += 2;
            }

            if (in_array(
                $methodCall[$ptr], array(
                    ';',
                    ',',
                    '/',
                    ' ',
                    "\t",
                    "\r",
                    "\n",
                    ')',
                    '+',
                    '*',
                    '%',
                    '=',
                    '-',
                    '|'
                )
            ) || substr($methodCall, $ptr, strlen($this->rd)) === $this->rd
            ) {
                // break char found
                break;
            }

            if (!preg_match('/^([a-z0-9_]+)(\(.*?\))?/i', substr($methodCall, $ptr), $methMatch)) {
                break;
            }

            if (empty($methMatch[2])) {
                // property
                if ($curBlock === 'root') {
                    $output .= '->' . $methMatch[1];
                } else {
                    $output = '(($tmp = ' . $output . ') ? $tmp->' . $methMatch[1] . ' : null)';
                }
                $ptr += strlen($methMatch[1]);
            } else {
                // method
                if (substr($methMatch[2], 0, 2) === '()') {
                    $parsedCall = $methMatch[1] . '()';
                    $ptr += strlen($methMatch[1]) + 2;
                } else {
                    $parsedCall = $this->parseFunction($methodCall, $ptr, strlen($methodCall), false, 'method', $ptr);
                }
                if ($this->securityPolicy !== null) {
                    $argPos = strpos($parsedCall, '(');
                    $method = strtolower(substr($parsedCall, 0, $argPos));
                    $args   = substr($parsedCall, $argPos);
                    if ($curBlock === 'root') {
                        $output = '$this->getSecurityPolicy()->callMethod($this, ' . $output . ', ' . var_export($method, true) . ', array' . $args . ')';
                    } else {
                        $output = '(($tmp = ' . $output . ') ? $this->getSecurityPolicy()->callMethod($this, $tmp, ' . var_export($method, true) . ', array' . $args . ') : null)';
                    }
                } else {
                    if ($curBlock === 'root') {
                        $output .= '->' . $parsedCall;
                    } else {
                        $output = '(($tmp = ' . $output . ') ? $tmp->' . $parsedCall . ' : null)';
                    }
                }
            }
        }

        $pointer += $ptr;

        return $output;
    }

    /**
     * Parses a constant variable (a variable that doesn't contain another variable) and preprocesses it to save
     * runtime processing time.
     *
     * @param string $key      the variable to parse
     * @param string $curBlock the current parser-block being processed
     *
     * @return string parsed variable
     */
    protected function parseVarKey($key, $curBlock)
    {
        if ($key === '') {
            return '$this->scope';
        }
        if (substr($key, 0, 1) === '.') {
            $key = 'dwoo' . $key;
        }
        if (preg_match('#dwoo\.(get|post|server|cookies|session|env|request)((?:\.[a-z0-9_-]+)+)#i', $key, $m)) {
            $global = strtoupper($m[1]);
            if ($global === 'COOKIES') {
                $global = 'COOKIE';
            }
            $key = '$_' . $global;
            foreach (explode('.', ltrim($m[2], '.')) as $part) {
                $key .= '[' . var_export($part, true) . ']';
            }
            if ($curBlock === 'root') {
                $output = $key;
            } else {
                $output = '(isset(' . $key . ')?' . $key . ':null)';
            }
        } elseif (preg_match('#dwoo\\.const\\.([a-z0-9\\\\_:]+)#i', $key, $m)) {
            return $this->parseConstKey($m[1], $curBlock);
        } elseif ($this->scope !== null) {
            if (strstr($key, '.') === false && strstr($key, '[') === false && strstr($key, '->') === false) {
                if ($key === 'dwoo') {
                    $output = '$this->globals';
                } elseif ($key === '_root' || $key === '__') {
                    $output = '$this->data';
                } elseif ($key === '_parent' || $key === '_') {
                    $output = '$this->readParentVar(1)';
                } elseif ($key === '_key') {
                    $output = '$tmp_key';
                } else {
                    if ($curBlock === 'root') {
                        $output = '$this->scope["' . $key . '"]';
                    } else {
                        $output = '(isset($this->scope["' . $key . '"]) ? $this->scope["' . $key . '"] : null)';
                    }
                }
            } else {
                preg_match_all('#(\[|->|\.)?((?:[a-z0-9_]|-(?!>))+|(\\\?[\'"])[^\3]*?\3)\]?#i', $key, $m);

                $i = $m[2][0];
                if ($i === '_parent' || $i === '_') {
                    $parentCnt = 0;

                    while (true) {
                        ++ $parentCnt;
                        array_shift($m[2]);
                        array_shift($m[1]);
                        if (current($m[2]) === '_parent') {
                            continue;
                        }
                        break;
                    }

                    $output = '$this->readParentVar(' . $parentCnt . ')';
                } else {
                    if ($i === 'dwoo') {
                        $output = '$this->globals';
                        array_shift($m[2]);
                        array_shift($m[1]);
                    } elseif ($i === '_root' || $i === '__') {
                        $output = '$this->data';
                        array_shift($m[2]);
                        array_shift($m[1]);
                    } elseif ($i === '_key') {
                        $output = '$tmp_key';
                    } else {
                        $output = '$this->scope';
                    }

                    while (count($m[1]) && $m[1][0] !== '->') {
                        $m[2][0] = preg_replace('/(^\\\([\'"])|\\\([\'"])$)/x', '$2$3', $m[2][0]);
                        if (substr($m[2][0], 0, 1) == '"' || substr($m[2][0], 0, 1) == "'") {
                            $output .= '[' . $m[2][0] . ']';
                        } else {
                            $output .= '["' . $m[2][0] . '"]';
                        }
                        array_shift($m[2]);
                        array_shift($m[1]);
                    }

                    if ($curBlock !== 'root') {
                        $output = '(isset(' . $output . ') ? ' . $output . ':null)';
                    }
                }

                if (count($m[2])) {
                    unset($m[0]);
                    $output = '$this->readVarInto(' . str_replace("\n", '', var_export($m, true)) . ', ' . $output . ', ' . ($curBlock == 'root' ? 'false' : 'true') . ')';
                }
            }
        } else {
            preg_match_all('#(\[|->|\.)?((?:[a-z0-9_]|-(?!>))+)\]?#i', $key, $m);
            unset($m[0]);
            $output = '$this->readVar(' . str_replace("\n", '', var_export($m, true)) . ')';
        }

        return $output;
    }

    /**
     * Flattens a variable tree, this helps in parsing very complex variables such as $var.foo[$foo.bar->baz].baz,
     * it computes the contents of the brackets first and works out from there.
     *
     * @param array $tree     the variable tree parsed by he parseVar() method that must be flattened
     * @param bool  $recursed leave that to false by default, it is only for internal use
     *
     * @return string flattened tree
     */
    protected function flattenVarTree(array $tree, $recursed = false)
    {
        $out = $recursed ? '".$this->readVarInto(' : '';
        foreach ($tree as $bit) {
            if (is_array($bit)) {
                $out .= '.' . $this->flattenVarTree($bit, false);
            } else {
                $key = str_replace('"', '\\"', $bit);

                if (substr($key, 0, 1) === '$') {
                    $out .= '".' . $this->parseVar($key, 0, strlen($key), false, 'variable') . '."';
                } else {
                    $cnt = substr_count($key, '$');

                    if ($this->debug) {
                        echo 'PARSING SUBVARS IN : ' . $key . "\n";
                    }
                    if ($cnt > 0) {
                        while (-- $cnt >= 0) {
                            if (isset($last)) {
                                $last = strrpos($key, '$', - (strlen($key) - $last + 1));
                            } else {
                                $last = strrpos($key, '$');
                            }
                            preg_match('#\$[a-z0-9_]+((?:(?:\.|->)(?:[a-z0-9_]+|(?R))|\[(?:[a-z0-9_]+|(?R))\]))*' . '((?:(?:[+/*%-])(?:\$[a-z0-9.[\]>_:-]+(?:\([^)]*\))?|[0-9.,]*))*)#i', substr($key, $last), $submatch);

                            $len = strlen($submatch[0]);
                            $key = substr_replace(
                                $key, preg_replace_callback(
                                    '#(\$[a-z0-9_]+((?:(?:\.|->)(?:[a-z0-9_]+|(?R))|\[(?:[a-z0-9_]+|(?R))\]))*)' . '((?:(?:[+/*%-])(?:\$[a-z0-9.[\]>_:-]+(?:\([^)]*\))?|[0-9.,]*))*)#i', array(
                                        $this,
                                        'replaceVarKeyHelper'
                                    ), substr($key, $last, $len)
                                ), $last, $len
                            );
                            if ($this->debug) {
                                echo 'RECURSIVE VAR REPLACEMENT DONE : ' . $key . "\n";
                            }
                        }
                        unset($last);

                        $out .= $key;
                    } else {
                        $out .= $key;
                    }
                }
            }
        }
        $out .= $recursed ? ', true)."' : '';

        return $out;
    }

    /**
     * Helper function that parses a variable.
     *
     * @param array $match the matched variable, array(1=>"string match")
     *
     * @return string parsed variable
     */
    protected function replaceVarKeyHelper($match)
    {
        return '".' . $this->parseVar($match[0], 0, strlen($match[0]), false, 'variable') . '."';
    }

    /**
     * Parses various constants, operators or non-quoted strings.
     *
     * @param string $in            the string within which we must parse something
     * @param int    $from          the starting offset of the parsed area
     * @param int    $to            the ending offset of the parsed area
     * @param mixed  $parsingParams must be an array if we are parsing a function or modifier's parameters, or false by
     *                              default
     * @param string $curBlock      the current parser-block being processed
     * @param mixed  $pointer       a reference to a pointer that will be increased by the amount of characters parsed,
     *                              or null by default
     *
     * @return string parsed values
     * @throws Exception
     */
    protected function parseOthers($in, $from, $to, $parsingParams = false, $curBlock = '', &$pointer = null)
    {
        $substr = substr($in, $from, $to - $from);

        $end = strlen($substr);

        if ($curBlock === 'condition') {
            $breakChars = array(
                '(',
                ')',
                ' ',
                '||',
                '&&',
                '|',
                '&',
                '>=',
                '<=',
                '===',
                '==',
                '=',
                '!==',
                '!=',
                '<<',
                '<',
                '>>',
                '>',
                '^',
                '~',
                ',',
                '+',
                '-',
                '*',
                '/',
                '%',
                '!',
                '?',
                ':',
                $this->rd,
                ';'
            );
        } elseif ($curBlock === 'modifier') {
            $breakChars = array(' ', ',', ')', ':', '|', "\r", "\n", "\t", ';', $this->rd);
        } elseif ($curBlock === 'expression') {
            $breakChars = array('/', '%', '+', '-', '*', ' ', ',', ')', "\r", "\n", "\t", ';', $this->rd);
        } else {
            $breakChars = array(' ', ',', ')', "\r", "\n", "\t", ';', $this->rd);
        }

        $breaker = false;
        foreach ($breakChars as $k => $char) {
            $test = strpos($substr, $char);
            if ($test !== false && $test < $end) {
                $end     = $test;
                $breaker = $k;
            }
        }

        if ($curBlock === 'condition') {
            if ($end === 0 && $breaker !== false) {
                $end = strlen($breakChars[$breaker]);
            }
        }

        if ($end !== false) {
            $substr = substr($substr, 0, $end);
        }

        if ($pointer !== null) {
            $pointer += strlen($substr);
        }

        $src    = $substr;
        $substr = trim($substr);

        if (strtolower($substr) === 'false' || strtolower($substr) === 'no' || strtolower($substr) === 'off') {
            if ($this->debug) {
                echo 'BOOLEAN(FALSE) PARSED' . "\n";
            }
            $substr = 'false';
            $type   = self::T_BOOL;
        } elseif (strtolower($substr) === 'true' || strtolower($substr) === 'yes' || strtolower($substr) === 'on') {
            if ($this->debug) {
                echo 'BOOLEAN(TRUE) PARSED' . "\n";
            }
            $substr = 'true';
            $type   = self::T_BOOL;
        } elseif ($substr === 'null' || $substr === 'NULL') {
            if ($this->debug) {
                echo 'NULL PARSED' . "\n";
            }
            $substr = 'null';
            $type   = self::T_NULL;
        } elseif (is_numeric($substr)) {
            $substr = (float)$substr;
            if ((int)$substr == $substr) {
                $substr = (int)$substr;
            }
            $type = self::T_NUMERIC;
            if ($this->debug) {
                echo 'NUMBER (' . $substr . ') PARSED' . "\n";
            }
        } elseif (preg_match('{^-?(\d+|\d*(\.\d+))\s*([/*%+-]\s*-?(\d+|\d*(\.\d+)))+$}', $substr)) {
            if ($this->debug) {
                echo 'SIMPLE MATH PARSED . "\n"';
            }
            $type   = self::T_MATH;
            $substr = '(' . $substr . ')';
        } elseif ($curBlock === 'condition' && array_search($substr, $breakChars, true) !== false) {
            if ($this->debug) {
                echo 'BREAKCHAR (' . $substr . ') PARSED' . "\n";
            }
            $type = self::T_BREAKCHAR;
            //$substr = '"'.$substr.'"';
        } else {
            $substr = $this->replaceStringVars('\'' . str_replace('\'', '\\\'', $substr) . '\'', '\'', $curBlock);
            $type   = self::T_UNQUOTED_STRING;
            if ($this->debug) {
                echo 'BLABBER (' . $substr . ') CASTED AS STRING' . "\n";
            }
        }

        if (is_array($parsingParams)) {
            $parsingParams[] = array($substr, $src, $type);

            return $parsingParams;
        } elseif ($curBlock === 'namedparam') {
            return array($substr, $src, $type);
        } elseif ($curBlock === 'expression') {
            return $substr;
        } else {
            throw new Exception('Something went wrong');
        }
    }

    /**
     * Replaces variables within a parsed string.
     *
     * @param string $string   the parsed string
     * @param string $first    the first character parsed in the string, which is the string delimiter (' or ")
     * @param string $curBlock the current parser-block being processed
     *
     * @return string the original string with variables replaced
     */
    protected function replaceStringVars($string, $first, $curBlock = '')
    {
        $pos = 0;
        if ($this->debug) {
            echo 'STRING VAR REPLACEMENT : ' . $string . "\n";
        }
        // replace vars
        while (($pos = strpos($string, '$', $pos)) !== false) {
            $prev = substr($string, $pos - 1, 1);
            if ($prev === '\\') {
                ++ $pos;
                continue;
            }

            $var = $this->parse($string, $pos, null, false, ($curBlock === 'modifier' ? 'modifier' : ($prev === '`' ? 'delimited_string' : 'string')));
            $len = $var[0];
            $var = $this->parse(str_replace('\\' . $first, $first, $string), $pos, null, false, ($curBlock === 'modifier' ? 'modifier' : ($prev === '`' ? 'delimited_string' : 'string')));

            if ($prev === '`' && substr($string, $pos + $len, 1) === '`') {
                $string = substr_replace($string, $first . '.' . $var[1] . '.' . $first, $pos - 1, $len + 2);
            } else {
                $string = substr_replace($string, $first . '.' . $var[1] . '.' . $first, $pos, $len);
            }
            $pos += strlen($var[1]) + 2;
            if ($this->debug) {
                echo 'STRING VAR REPLACEMENT DONE : ' . $string . "\n";
            }
        }

        // handle modifiers
        // TODO Obsolete?
        $string = preg_replace_callback(
            '#("|\')\.(.+?)\.\1((?:\|(?:@?[a-z0-9_]+(?:(?::("|\').+?\4|:[^`]*))*))+)#i', array(
            $this,
            'replaceModifiers'
            ), $string
        );

        // replace escaped dollar operators by unescaped ones if required
        if ($first === "'") {
            $string = str_replace('\\$', '$', $string);
        }

        return $string;
    }

    /**
     * Replaces the modifiers applied to a string or a variable.
     *
     * @param array  $m        the regex matches that must be array(1=>"double or single quotes enclosing a string,
     *                         when applicable", 2=>"the string or var", 3=>"the modifiers matched")
     * @param string $curBlock the current parser-block being processed
     * @param null   $pointer
     *
     * @return string the input enclosed with various function calls according to the modifiers found
     * @throws CompilationException
     * @throws Exception
     */
    protected function replaceModifiers(array $m, $curBlock = null, &$pointer = null)
    {
        if ($this->debug) {
            echo 'PARSING MODIFIERS : ' . $m[3] . "\n";
        }

        if ($pointer !== null) {
            $pointer += strlen($m[3]);
        }
        // remove first pipe
        $cmdstrsrc = substr($m[3], 1);
        // remove last quote if present
        if (substr($cmdstrsrc, - 1, 1) === $m[1]) {
            $cmdstrsrc = substr($cmdstrsrc, 0, - 1);
            $add       = $m[1];
        }

        $output = $m[2];

        $continue = true;
        while (strlen($cmdstrsrc) > 0 && $continue) {
            if ($cmdstrsrc[0] === '|') {
                $cmdstrsrc = substr($cmdstrsrc, 1);
                continue;
            }
            if ($cmdstrsrc[0] === ' ' || $cmdstrsrc[0] === ';' || substr($cmdstrsrc, 0, strlen($this->rd)) === $this->rd) {
                if ($this->debug) {
                    echo 'MODIFIER PARSING ENDED, RIGHT DELIMITER or ";" FOUND' . "\n";
                }
                $continue = false;
                if ($pointer !== null) {
                    $pointer -= strlen($cmdstrsrc);
                }
                break;
            }
            $cmdstr   = $cmdstrsrc;
            $paramsep = ':';
            if (!preg_match('/^(@{0,2}[a-z_][a-z0-9_]*)(:)?/i', $cmdstr, $match)) {
                throw new CompilationException($this, 'Invalid modifier name, started with : ' . substr($cmdstr, 0, 10));
            }
            $paramspos = !empty($match[2]) ? strlen($match[1]) : false;
            $func      = $match[1];

            $state = 0;
            if ($paramspos === false) {
                $cmdstrsrc = substr($cmdstrsrc, strlen($func));
                $params    = array();
                if ($this->debug) {
                    echo 'MODIFIER (' . $func . ') CALLED WITH NO PARAMS' . "\n";
                }
            } else {
                $paramstr = substr($cmdstr, $paramspos + 1);
                if (substr($paramstr, - 1, 1) === $paramsep) {
                    $paramstr = substr($paramstr, 0, - 1);
                }

                $ptr    = 0;
                $params = array();
                while ($ptr < strlen($paramstr)) {
                    if ($this->debug) {
                        echo 'MODIFIER (' . $func . ') START PARAM PARSING WITH POINTER AT ' . $ptr . "\n";
                    }
                    if ($this->debug) {
                        echo $paramstr . '--' . $ptr . '--' . strlen($paramstr) . '--modifier' . "\n";
                    }
                    $params = $this->parse($paramstr, $ptr, strlen($paramstr), $params, 'modifier', $ptr);
                    if ($this->debug) {
                        echo 'PARAM PARSED, POINTER AT ' . $ptr . "\n";
                    }

                    if ($ptr >= strlen($paramstr)) {
                        if ($this->debug) {
                            echo 'PARAM PARSING ENDED, PARAM STRING CONSUMED' . "\n";
                        }
                        break;
                    }

                    if ($paramstr[$ptr] === ' ' || $paramstr[$ptr] === '|' || $paramstr[$ptr] === ';' || substr($paramstr, $ptr, strlen($this->rd)) === $this->rd) {
                        if ($this->debug) {
                            echo 'PARAM PARSING ENDED, " ", "|", RIGHT DELIMITER or ";" FOUND, POINTER AT ' . $ptr . "\n";
                        }
                        if ($paramstr[$ptr] !== '|') {
                            $continue = false;
                            if ($pointer !== null) {
                                $pointer -= strlen($paramstr) - $ptr;
                            }
                        }
                        ++ $ptr;
                        break;
                    }
                    if ($ptr < strlen($paramstr) && $paramstr[$ptr] === ':') {
                        ++ $ptr;
                    }
                }
                $cmdstrsrc = substr($cmdstrsrc, strlen($func) + 1 + $ptr);
                foreach ($params as $k => $p) {
                    if (is_array($p) && is_array($p[1])) {
                        $state |= 2;
                    } else {
                        if (($state & 2) && preg_match('#^(["\'])(.+?)\1$#', $p[0], $m)) {
                            $params[$k] = array($m[2], array('true', 'true'));
                        } else {
                            if ($state & 2) {
                                throw new CompilationException($this, 'You can not use an unnamed parameter after a named one');
                            }
                            $state |= 1;
                        }
                    }
                }
            }

            // check if we must use array_map with this plugin or not
            $mapped = false;
            if (substr($func, 0, 1) === '@') {
                $func   = substr($func, 1);
                $mapped = true;
            }

            $pluginType = $this->getPluginType($func);

            if ($state & 2) {
                array_unshift($params, array('value', is_array($output) ? $output : array($output, $output)));
            } else {
                array_unshift($params, is_array($output) ? $output : array($output, $output));
            }

            if ($pluginType & Core::NATIVE_PLUGIN) {
                $params = $this->mapParams($params, null, $state);

                $params = $params['*'][0];

                $params = self::implode_r($params);

                if ($mapped) {
                    $output = '$this->arrayMap(\'' . $func . '\', array(' . $params . '))';
                } else {
                    $output = $func . '(' . $params . ')';
                }
            } elseif ($pluginType & Core::PROXY_PLUGIN) {
                $params = $this->mapParams($params, $this->getCore()->getPluginProxy()->getCallback($func), $state);
                foreach ($params as &$p) {
                    $p = $p[0];
                }
                $output = call_user_func(array($this->getCore()->getPluginProxy(), 'getCode'), $func, $params);
            } elseif ($pluginType & Core::SMARTY_MODIFIER) {
                $params = $this->mapParams($params, null, $state);
                $params = $params['*'][0];

                $params = self::implode_r($params);

                if ($pluginType & Core::CUSTOM_PLUGIN) {
                    $callback = $this->customPlugins[$func]['callback'];
                    if (is_array($callback)) {
                        if (is_object($callback[0])) {
                            $output = ($mapped ? '$this->arrayMap' : 'call_user_func_array') . '(array($this->getCustomPlugin(\'' . $func . '\'), \'' . $callback[1] . '\'), array(' . $params . '))';
                        } else {
                            $output = ($mapped ? '$this->arrayMap' : 'call_user_func_array') . '(array(\'' . $callback[0] . '\', \'' . $callback[1] . '\'), array(' . $params . '))';
                        }
                    } elseif ($mapped) {
                        $output = '$this->arrayMap(\'' . $callback . '\', array(' . $params . '))';
                    } else {
                        $output = $callback . '(' . $params . ')';
                    }
                } elseif ($mapped) {
                    $output = '$this->arrayMap(\'smarty_modifier_' . $func . '\', array(' . $params . '))';
                } else {
                    $output = 'smarty_modifier_' . $func . '(' . $params . ')';
                }
            } else {
                if ($pluginType & Core::CUSTOM_PLUGIN) {
                    $pluginName = $callback = $this->customPlugins[$func]['callback'];
                    if (($pluginType & Core::CLASS_PLUGIN) && !is_array($callback)) {
                        $pluginName = $this->customPlugins[$func]['callback'];
                        $callback   = array($pluginName, ($pluginType & Core::COMPILABLE_PLUGIN) ? 'compile' : 'process');
                    }
                } else {
                    if (class_exists('Plugin' . Core::toCamelCase($func)) !== false || function_exists('Plugin' .
                            Core::toCamelCase($func) . (($pluginType & Core::COMPILABLE_PLUGIN) ? 'Compile' : ''))
                        !== false) {
                        $pluginName = 'Plugin' . Core::toCamelCase($func);
                    } else {
                        $pluginName = Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func);
                    }
                    if ($pluginType & Core::CLASS_PLUGIN) {
                        $callback = array($pluginName, ($pluginType & Core::COMPILABLE_PLUGIN) ? 'compile' : 'process');
                    } else {
                        $callback = $pluginName . (($pluginType & Core::COMPILABLE_PLUGIN) ? 'Compile' : '');
                    }
                }
                $params = $this->mapParams($params, $callback, $state);

                foreach ($params as &$p) {
                    $p = $p[0];
                }

                // Only for PHP function, who is not a PHP class
                if ($pluginType & Core::FUNC_PLUGIN && !($pluginType & Core::CLASS_PLUGIN)) {
                    if ($pluginType & Core::COMPILABLE_PLUGIN) {
                        if ($mapped) {
                            throw new CompilationException($this, 'The @ operator can not be used on compiled plugins.');
                        }
                        if ($pluginType & Core::CUSTOM_PLUGIN) {
                            $funcCompiler = $this->customPlugins[$func]['callback'];
                        } else {
                            if (function_exists('Plugin' . Core::toCamelCase($func) . 'Compile') !== false) {
                                $funcCompiler = 'Plugin' . Core::toCamelCase($func) . 'Compile';
                            } else {
                                $funcCompiler = Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func) .
                                    'Compile';
                            }
                        }
                        array_unshift($params, $this);
                        $output = call_user_func_array($funcCompiler, $params);
                    } else {
                        array_unshift($params, '$this');

                        $params = self::implode_r($params);
                        if ($mapped) {
                            $output = '$this->arrayMap(\'' . $pluginName . '\', array(' . $params . '))';
                        } else {
                            $output = $pluginName . '(' . $params . ')';
                        }
                    }
                } else {
                    if ($pluginType & Core::COMPILABLE_PLUGIN) {
                        if ($mapped) {
                            throw new CompilationException($this, 'The @ operator can not be used on compiled plugins.');
                        }
                        if ($pluginType & Core::CUSTOM_PLUGIN) {
                            $callback = $this->customPlugins[$func]['callback'];
                            if (!is_array($callback)) {
                                if (!method_exists($callback, 'compile')) {
                                    throw new Exception('Custom plugin ' . $func . ' must implement the "compile" method to be compilable, or you should provide a full callback to the method to use');
                                }
                                if (($ref = new ReflectionMethod($callback, 'compile')) && $ref->isStatic()) {
                                    $funcCompiler = array($callback, 'compile');
                                } else {
                                    $funcCompiler = array(new $callback(), 'compile');
                                }
                            } else {
                                $funcCompiler = $callback;
                            }
                        } else {
                            if (class_exists('Plugin' . Core::toCamelCase($func)) !== false) {
                                $funcCompiler = array('Plugin' . Core::toCamelCase($func), 'compile');
                            } else {
                                $funcCompiler = array(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func), 'compile');
                            }
                            array_unshift($params, $this);
                        }
                        $output = call_user_func_array($funcCompiler, $params);
                    } else {
                        $params = self::implode_r($params);

                        if ($pluginType & Core::CUSTOM_PLUGIN) {
                            if (is_object($callback[0])) {
                                if (is_array($this->getCore()->getCustomPlugin($func))) {
                                    $output = ($mapped ? '$this->arrayMap' : 'call_user_func_array') . '(array($this->plugins[\'' . $func . '\'][\'callback\'][0], \'' . $callback[1] . '\'), array(' . $params . '))';
                                } else {
                                    $output = ($mapped ? '$this->arrayMap' : 'call_user_func_array') . '(array($this->getCustomPlugin(\'' . $func . '\'), \'' . $callback[1] . '\'), array(' . $params . '))';
                                }
                            } else {
                                $output = ($mapped ? '$this->arrayMap' : 'call_user_func_array') . '(array(\'' . $callback[0] . '\', \'' . $callback[1] . '\'), array(' . $params . '))';
                            }
                        } elseif ($mapped) {
                            $output = '$this->arrayMap(array($this->getObjectPlugin(\''.
                                Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func) . '\'), 
                            \'process\'), array(' . $params . '))';
                        } else {
                            if (class_exists('Plugin' . Core::toCamelCase($func)) !== false) {
                                $output = '$this->classCall(\'Plugin' . Core::toCamelCase($func) . '\', array(' . $params . '))';
                            } elseif (class_exists(Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($func)) !== false) {
                                $output = '$this->classCall(\'' . Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . $func . '\', array(' . $params . '))';
                            } elseif (class_exists(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($func)) !== false) {
                                $output = '$this->classCall(\'' . Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . $func . '\', array(' . $params . '))';
                            } else {
                                $output = '$this->classCall(\'' . $func . '\', array(' . $params . '))';
                            }
                        }
                    }
                }
            }
        }

        if ($curBlock === 'namedparam') {
            return array($output, $output);
        } elseif ($curBlock === 'var' || $m[1] === null) {
            return $output;
        } elseif ($curBlock === 'string' || $curBlock === 'root') {
            return $m[1] . '.' . $output . '.' . $m[1] . (isset($add) ? $add : null);
        }

        return '';
    }

    /**
     * Recursively implodes an array in a similar manner as var_export() does but with some tweaks
     * to handle pre-compiled values and the fact that we do not need to enclose everything with
     * "array" and do not require top-level keys to be displayed.
     *
     * @param array $params        the array to implode
     * @param bool  $recursiveCall if set to true, the function outputs key names for the top level
     *
     * @return string the imploded array
     */
    public static function implode_r(array $params, $recursiveCall = false)
    {
        $out = '';
        foreach ($params as $k => $p) {
            if (is_array($p)) {
                $out2 = 'array(';
                foreach ($p as $k2 => $v) {
                    $out2 .= var_export($k2, true) . ' => ' . (is_array($v) ? 'array(' . self::implode_r($v, true) . ')' : $v) . ', ';
                }
                $p = rtrim($out2, ', ') . ')';
            }
            if ($recursiveCall) {
                $out .= var_export($k, true) . ' => ' . $p . ', ';
            } else {
                $out .= $p . ', ';
            }
        }

        return rtrim($out, ', ');
    }

    /**
     * Returns the plugin type of a plugin and adds it to the used plugins array if required.
     *
     * @param string $name plugin name, as found in the template
     *
     * @return int type as a multi bit flag composed of the Dwoo plugin types constants
     * @throws Exception
     * @throws SecurityException
     * @throws Exception
     */
    protected function getPluginType($name)
    {
        $pluginType = - 1;

        if (($this->securityPolicy === null && (function_exists($name) || strtolower($name) === 'isset' || strtolower($name) === 'empty')) || ($this->securityPolicy !== null && array_key_exists(strtolower($name), $this->securityPolicy->getAllowedPhpFunctions()) !== false)) {
            $phpFunc = true;
        } elseif ($this->securityPolicy !== null && function_exists($name) && array_key_exists(strtolower($name), $this->securityPolicy->getAllowedPhpFunctions()) === false) {
            throw new SecurityException('Call to a disallowed php function : ' . $name);
        }

        while ($pluginType <= 0) {
            // Template plugin compilable
            if (isset($this->templatePlugins[$name])) {
                $pluginType = Core::TEMPLATE_PLUGIN | Core::COMPILABLE_PLUGIN;
            } // Custom plugin
            elseif (isset($this->customPlugins[$name])) {
                $pluginType = $this->customPlugins[$name]['type'] | Core::CUSTOM_PLUGIN;
            } // Class blocks plugin
            elseif (class_exists(Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($name)) !== false) {
                $pluginType = Core::CLASS_PLUGIN;
                if (is_subclass_of(Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($name), 'Dwoo\Block\Plugin')) {
                    $pluginType += Core::BLOCK_PLUGIN;
                }
                $interfaces = class_implements(Core::NAMESPACE_PLUGINS_BLOCKS . 'Plugin' . Core::toCamelCase($name));
                if (in_array('Dwoo\ICompilable', $interfaces) !== false || in_array('Dwoo\ICompilable\Block', $interfaces) !== false) {
                    $pluginType |= Core::COMPILABLE_PLUGIN;
                }
            } // Class functions plugin
            elseif (class_exists(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($name)) !== false) {
                $pluginType = Core::FUNC_PLUGIN + Core::CLASS_PLUGIN;
                $interfaces = class_implements(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($name));
                if (in_array('Dwoo\ICompilable', $interfaces) !== false || in_array('Dwoo\ICompilable\Block', $interfaces) !== false) {
                    $pluginType |= Core::COMPILABLE_PLUGIN;
                }
            } // Class without namespace
            elseif (class_exists('Plugin' . Core::toCamelCase($name)) !== false) {
                $pluginType = Core::CLASS_PLUGIN;
                $interfaces = class_implements('Plugin' . Core::toCamelCase($name));
                if (in_array('Dwoo\ICompilable', $interfaces) !== false || in_array('Dwoo\ICompilable\Block', $interfaces) !== false) {
                    $pluginType |= Core::COMPILABLE_PLUGIN;
                }
            } // Function plugin (with/without namespaces)
            elseif (function_exists(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase ($name)) !==
                false || function_exists('Plugin' . Core::toCamelCase($name)) !== false) {
                $pluginType = Core::FUNC_PLUGIN;
            } // Function plugin compile (with/without namespaces)
            elseif (function_exists(Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin' . Core::toCamelCase($name) .
                    'Compile') !== false || function_exists('Plugin' . Core::toCamelCase($name) . 'Compile') !==
                false) {
                $pluginType = Core::FUNC_PLUGIN | Core::COMPILABLE_PLUGIN;
            } // Helper plugin class compile
            elseif (class_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($name)) !== false) {
                $pluginType = Core::CLASS_PLUGIN | Core::COMPILABLE_PLUGIN;
            } // Helper plugin function compile
            elseif (function_exists(Core::NAMESPACE_PLUGINS_HELPERS . 'Plugin' . Core::toCamelCase($name) . 'Compile') !== false) {
                $pluginType = Core::FUNC_PLUGIN | Core::COMPILABLE_PLUGIN;
            } // Smarty modifier
            elseif (function_exists('smarty_modifier_' . $name) !== false) {
                $pluginType = Core::SMARTY_MODIFIER;
            } // Smarty function
            elseif (function_exists('smarty_function_' . $name) !== false) {
                $pluginType = Core::SMARTY_FUNCTION;
            } // Smarty block
            elseif (function_exists('smarty_block_' . $name) !== false) {
                $pluginType = Core::SMARTY_BLOCK;
            } // Everything else
            else {
                if ($pluginType === - 1) {
                    try {
                        $this->getCore()->getLoader()->loadPlugin('Plugin' . Core::toCamelCase($name));
                    }
                    catch (Exception $e) {
                        if (isset($phpFunc)) {
                            $pluginType = Core::NATIVE_PLUGIN;
                        } elseif (is_object($this->getCore()->getPluginProxy()) && $this->getCore()->getPluginProxy()->handles($name)) {
                            $pluginType = Core::PROXY_PLUGIN;
                            break;
                        } else {
                            throw $e;
                        }
                    }
                } else {
                    throw new Exception('Plugin "' . $name . '" could not be found, type:' . $pluginType);
                }
                ++ $pluginType;
            }
        }

        if (($pluginType & Core::COMPILABLE_PLUGIN) === 0 && ($pluginType & Core::NATIVE_PLUGIN) === 0 && ($pluginType & Core::PROXY_PLUGIN) === 0) {
            $this->addUsedPlugin(Core::toCamelCase($name), $pluginType);
        }

        return $pluginType;
    }

    /**
     * Allows a plugin to load another one at compile time, this will also mark
     * it as used by this template so it will be loaded at runtime (which can be
     * useful for compiled plugins that rely on another plugin when their compiled
     * code runs).
     *
     * @param string $name the plugin name
     *
     * @return void
     */
    public function loadPlugin($name)
    {
        $this->getPluginType($name);
    }

    /**
     * Runs htmlentities over the matched <?php ?> blocks when the security policy enforces that.
     *
     * @param array $match matched php block
     *
     * @return string the htmlentities-converted string
     */
    protected function phpTagEncodingHelper($match)
    {
        return htmlspecialchars($match[0]);
    }

    /**
     * Maps the parameters received from the template onto the parameters required by the given callback.
     *
     * @param array    $params   the array of parameters
     * @param callback $callback the function or method to reflect on to find out the required parameters
     * @param int      $callType the type of call in the template, 0 = no params, 1 = php-style call, 2 = named
     *                           parameters call
     * @param array    $map      the parameter map to use, if not provided it will be built from the callback
     *
     * @return array parameters sorted in the correct order with missing optional parameters filled
     * @throws CompilationException
     */
    protected function mapParams(array $params, $callback, $callType = 2, $map = null)
    {
        if (!$map) {
            $map = $this->getParamMap($callback);
        }

        $paramlist = array();

        // transforms the parameter array from (x=>array('paramname'=>array(values))) to (paramname=>array(values))
        $ps = array();
        foreach ($params as $p) {
            if (is_array($p[1])) {
                $ps[$p[0]] = $p[1];
            } else {
                $ps[] = $p;
            }
        }

        // loops over the param map and assigns values from the template or default value for unset optional params
        foreach ($map as $k => $v){
            if ($v[0] === '*') {
                // "rest" array parameter, fill every remaining params in it and then break
                if (count($ps) === 0) {
                    if ($v[1] === false) {
                        throw new CompilationException(
                            $this, 'Rest argument missing for ' . str_replace(
                                array(
                                    Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin',
                                'Compile'
                                ), '', (is_array($callback) ? $callback[0] : $callback)
                            )
                        );
                    } else {
                        break;
                    }
                }
                $tmp  = array();
                $tmp2 = array();
                $tmp3 = array();
                foreach ($ps as $i => $p) {
                    $tmp[$i]  = $p[0];
                    $tmp2[$i] = $p[1];
                    $tmp3[$i] = isset($p[2]) ? $p[2] : 0;
                    unset($ps[$i]);
                }
                $paramlist[$v[0]] = array($tmp, $tmp2, $tmp3);
                unset($tmp, $tmp2, $i, $p);
                break;
            } elseif (isset($ps[$v[0]])) {
                // parameter is defined as named param
                $paramlist[$v[0]] = $ps[$v[0]];
                unset($ps[$v[0]]);
            } elseif (isset($ps[$k])) {
                // parameter is defined as ordered param
                $paramlist[$v[0]] = $ps[$k];
                unset($ps[$k]);
            } elseif ($v[1] === false) {
                // parameter is not defined and not optional, throw error
                if (is_array($callback)) {
                    if (is_object($callback[0])) {
                        $name = get_class($callback[0]) . '::' . $callback[1];
                    } else {
                        $name = $callback[0];
                    }
                } else {
                    $name = $callback;
                }

                throw new CompilationException(
                    $this, 'Argument ' . $k . '/' . $v[0] . ' missing for ' . str_replace(
                        array(
                            Core::NAMESPACE_PLUGINS_FUNCTIONS . 'Plugin',
                        'Compile'
                        ), '', $name
                    )
                );
            } elseif ($v[2] === null) {
                // enforce lowercased null if default value is null (php outputs NULL with var export)
                $paramlist[$v[0]] = array('null', null, self::T_NULL);
            } else {
                // outputs default value with var_export
                $paramlist[$v[0]] = array(var_export($v[2], true), $v[2]);
            }
        }

        if (count($ps)) {
            foreach ($ps as $i => $p) {
                array_push($paramlist, $p);
            }
        }

        return $paramlist;
    }

    /**
     * Returns the parameter map of the given callback, it filters out entries typed as Dwoo and Compiler and turns the
     * rest parameter into a "*".
     *
     * @param callback $callback the function/method to reflect on
     *
     * @return array processed parameter map
     */
    protected function getParamMap($callback)
    {
        if (is_null($callback)) {
            return array(array('*', true));
        }
        if (is_array($callback)) {
            $ref = new ReflectionMethod($callback[0], $callback[1]);
        } else {
            $ref = new ReflectionFunction($callback);
        }

        $out = array();
        foreach ($ref->getParameters() as $param) {
            if (($class = $param->getClass()) !== null && $class->name === 'Dwoo\Core') {
                continue;
            }
            if (($class = $param->getClass()) !== null && $class->name === 'Dwoo\Compiler') {
                continue;
            }
            if ($param->getName() === 'rest' && $param->isArray() === true) {
                $out[] = array('*', $param->isOptional(), null);
                continue;
            }
            $out[] = array(
                $param->getName(),
                $param->isOptional(),
                $param->isOptional() ? $param->getDefaultValue() : null
            );
        }

        return $out;
    }

    /**
     * Returns a default instance of this compiler, used by default by all Dwoo templates that do not have a
     * specific compiler assigned and when you do not override the default compiler factory function.
     *
     * @see    Core::setDefaultCompilerFactory()
     * @return Compiler
     */
    public static function compilerFactory()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
