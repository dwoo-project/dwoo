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
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.4
 * @date      2017-03-07
 * @link      http://dwoo.org/
 */

namespace Dwoo;

/**
 * Interface that represents a dwoo template.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
interface ITemplate
{
    /**
     * Returns the cache duration for this template.
     * defaults to null if it was not provided
     *
     * @return int|null
     */
    public function getCacheTime();

    /**
     * Sets the cache duration for this template.
     * can be used to set it after the object is created if you did not provide
     * it in the constructor
     *
     * @param int $seconds duration of the cache validity for this template, if
     *                     null it defaults to the Dwoo instance's cache time. 0 = disable and
     *                     -1 = infinite cache
     */
    public function setCacheTime($seconds = null);

    /**
     * Returns the cached template output file name, true if it's cache-able but not cached
     * or false if it's not cached.
     *
     * @param Core $core the dwoo instance that requests it
     *
     * @return string|bool
     */
    public function getCachedTemplate(Core $core);

    /**
     * Caches the provided output into the cache file.
     *
     * @param Core   $core   the dwoo instance that requests it
     * @param string $output the template output
     *
     * @return mixed full path of the cached file or false upon failure
     */
    public function cache(Core $core, $output);

    /**
     * Clears the cached template if it's older than the given time.
     *
     * @param Core $core      the dwoo instance that was used to cache that template
     * @param int  $olderThan minimum time (in seconds) required for the cache to be cleared
     *
     * @return bool true if the cache was not present or if it was deleted, false if it remains there
     */
    public function clearCache(Core $core, $olderThan = - 1);

    /**
     * Returns the compiled template file name.
     *
     * @param Core      $core     the dwoo instance that requests it
     * @param ICompiler $compiler the compiler that must be used
     *
     * @return string
     */
    public function getCompiledTemplate(Core $core, ICompiler $compiler = null);

    /**
     * Returns the template name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the resource name for this template class.
     *
     * @return string
     */
    public function getResourceName();

    /**
     * Returns the resource identifier for this template or false if it has no identifier.
     *
     * @return string|false
     */
    public function getResourceIdentifier();

    /**
     * Returns the template source of this template.
     *
     * @return string
     */
    public function getSource();

    /**
     * Returns an unique string identifying the current version of this template,
     * for example a timestamp of the last modified date or a hash of the template source.
     *
     * @return string
     */
    public function getUid();

    /**
     * Returns the compiler used by this template, if it was just compiled, or null.
     *
     * @return ICompiler
     */
    public function getCompiler();

    /**
     * Returns some php code that will check if this template has been modified or not.
     * if the function returns null, the template will be instanciated and then the Uid checked
     *
     * @return string
     */
    public function getIsModifiedCode();

    /**
     * Returns a new template object from the given resource identifier, null if no include is
     * possible (resource not found), or false if include is not permitted by this resource type.
     * this method should also check if $dwoo->getSecurityPolicy() is null or not and do the
     * necessary permission checks if required, if the security policy prevents the template
     * generation it should throw a new Security\Exception with a relevant message
     *
     * @param Core      $core
     * @param mixed     $resourceId     the resource identifier
     * @param int       $cacheTime      duration of the cache validity for this template, if null it defaults to the
     *                                  Dwoo instance that will render this template if null it defaults to the Dwoo
     *                                  instance that will render this template
     * @param string    $cacheId        the unique cache identifier of this page or anything else that makes this
     *                                  template's content unique, if null it defaults to the current url makes this
     *                                  template's content unique, if null it defaults to the current url
     * @param string    $compileId      the unique compiled identifier, which is used to distinguish this template from
     *                                  others, if null it defaults to the filename+bits of the path template from
     *                                  others, if null it defaults to the filename+bits of the path
     * @param ITemplate $parentTemplate the template that is requesting a new template object (through an include,
     *                                  extends or any other plugin) an include, extends or any other plugin)
     *
     * @return ITemplate|false|null
     */
    public static function templateFactory(Core $core, $resourceId, $cacheTime = null, $cacheId = null,
                                           $compileId = null, ITemplate $parentTemplate = null);
}
