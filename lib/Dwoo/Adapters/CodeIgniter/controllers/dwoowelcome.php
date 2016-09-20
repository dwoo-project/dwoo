<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Adapters\CodeIgniter\controllers
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */

class dwoowelcome extends Controller
{
    public function __construct()
    {
        parent::Controller();
    }

    public function index()
    {
        $this->load->library('Dwootemplate');
        $this->dwootemplate->assign('itshowlate', date('H:i:s'));
        $this->dwootemplate->display('dwoowelcome.tpl');
    }
}
