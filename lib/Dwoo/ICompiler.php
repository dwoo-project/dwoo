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
 * @date      2017-03-21
 * @link      http://dwoo.org/
 */

namespace Dwoo;

use Dwoo\Security\Policy as SecurityPolicy;

/**
 * Interface that represents a dwoo compiler.
 * while implementing this is enough to interact with Dwoo/Templates, it is not
 * sufficient to interact with Dwoo/Plugins, however the main purpose of creating a
 * new compiler would be to interact with other/different plugins, that is why this
 * interface has been left with the minimum requirements.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
interface ICompiler
{
    /**
     * Compiles the provided string down to php code.
     *
     * @param Core      $core
     * @param ITemplate $template the template to compile
     *
     * @return string a compiled php code string
     */
    public function compile(Core $core, ITemplate $template);

    /**
     * Adds the custom plugins loaded into Dwoo to the compiler so it can load them.
     *
     * @see Core::addPlugin
     *
     * @param array $customPlugins an array of custom plugins
     */
    public function setCustomPlugins(array $customPlugins);

    /**
     * Sets the security policy object to enforce some php security settings.
     * use this if untrusted persons can modify templates,
     * set it on the Dwoo object as it will be passed onto the compiler automatically
     *
     * @param SecurityPolicy $policy the security policy object
     */
    public function setSecurityPolicy(SecurityPolicy $policy = null);
}
