<?php

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
