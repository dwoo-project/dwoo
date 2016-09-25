<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Adapters\CodeIgniter\config
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// The name of the directory where templates are located.
$config['template_dir'] = dirname(FCPATH).'/../application/views/';

// The directory where compiled templates are located
$config['compileDir'] = dirname(FCPATH).'/../compile/';

//This tells Dwoo whether or not to cache the output of the templates to the $cache_dir.
$config['caching'] = 0;
$config['cacheDir'] = dirname(FCPATH).'/../cache/';
$config['cacheTime'] = 0;
