<?php

/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Adapters\Agavi
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */
class DwooRenderer extends AgaviRenderer implements AgaviIReusableRenderer
{
    /**
     * @constant   string The directory inside the cache dir where templates will
     *                    be stored in compiled form.
     */
    const COMPILE_DIR = 'templates';

    /**
     * @constant   string The subdirectory inside the compile dir where templates
     *                    will be stored in compiled form.
     */
    const COMPILE_SUBDIR = 'dwoo';

    /**
     * @constant   string The directory inside the cache dir where cached content
     *                    will be stored.
     */
    const CACHE_DIR = 'dwoo';

    /**
     * @var Dwoo Dwoo template engine
     */
    protected $dwoo = null;

    /**
     * @var string A string with the default template file extension,
     *             including the dot
     */
    protected $defaultExtension = '.html';

    /**
     * stores the (optional) plugin directories to add to the Dwoo_Loader.
     */
    protected $plugin_dir = null;

    /**
     * Pre-serialization callback.
     *
     * Excludes the Dwoo instance to prevent excessive serialization load.
     */
    public function __sleep()
    {
        $keys = parent::__sleep();
        unset($keys[array_search('dwoo', $keys)]);

        return $keys;
    }

    /**
     * Initialize this Renderer.
     *
     * @param AgaviContext The current application context
     * @param array        An associative array of initialization parameters
     */
    public function initialize(AgaviContext $context, array $parameters = array())
    {
        parent::initialize($context, $parameters);

        $this->plugin_dir = $this->getParameter('plugin_dir', $this->plugin_dir);
    }

    /**
     * provides a custom compiler to the dwoo renderer with optional settings
     * you can set in the agavi output_types.xml config file.
     *
     * @return Dwoo_Compiler
     */
    public function compilerFactory()
    {
        $compiler = Dwoo_Compiler::compilerFactory();
        $compiler->setAutoEscape((bool) $this->getParameter('auto_escape', false));

        return $compiler;
    }

    /**
     * Grab a cleaned up dwoo instance.
     *
     * @return Dwoo A Dwoo instance
     */
    protected function getEngine()
    {
        if ($this->dwoo) {
            return $this->dwoo;
        }

        // this triggers Agavi autoload
        if (!class_exists('Dwoo')) {
            if (file_exists(dirname(__FILE__).'/../../../dwooAutoload.php')) {
                // file was dropped with the entire dwoo package
                include dirname(__FILE__).'/../../../dwooAutoload.php';
            } else {
                // assume the dwoo package is in the include path
                include 'dwooAutoload.php';
            }
        }

        $parentMode = fileperms(AgaviConfig::get('core.cache_dir'));

        $compileDir = AgaviConfig::get('core.cache_dir').DIRECTORY_SEPARATOR.self::COMPILE_DIR.DIRECTORY_SEPARATOR.self::COMPILE_SUBDIR;
        AgaviToolkit::mkdir($compileDir, $parentMode, true);

        $cacheDir = AgaviConfig::get('core.cache_dir').DIRECTORY_SEPARATOR.self::CACHE_DIR;
        AgaviToolkit::mkdir($cacheDir, $parentMode, true);

        $this->dwoo = new Dwoo_Core($compileDir, $cacheDir);

        if (!empty($this->plugin_dir)) {
            foreach ((array) $this->plugin_dir as $dir) {
                $this->dwoo->getLoader()->addDirectory($dir);
            }
        }

        $this->dwoo->setDefaultCompilerFactory('file', array($this, 'compilerFactory'));

        return $this->dwoo;
    }

    /**
     * Render the presentation and return the result.
     *
     * @param AgaviTemplateLayer The template layer to render
     * @param array              The template variables
     * @param array              The slots
     * @param array              Associative array of additional assigns
     *
     * @return string A rendered result
     */
    public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
    {
        $engine = $this->getEngine();

        $data = array();
        if ($this->extractVars) {
            $data = $attributes;
        } else {
            $data[$this->varName] = &$attributes;
        }

        $data[$this->slotsVarName] = &$slots;

        foreach ($this->assigns as $key => $getter) {
            $data[$key] = $this->getContext()->$getter();
        }

        foreach ($moreAssigns as $key => &$value) {
            if (isset($this->moreAssignNames[$key])) {
                $key = $this->moreAssignNames[$key];
            }
            $data[$key] = &$value;
        }

        return $engine->get($layer->getResourceStreamIdentifier(), $data);
    }
}
