<?php
// Include the main class (it should handle the rest on its own)
require 'lib/Dwoo/Autoloader.php';
Dwoo\Autoloader::register();

// Create the controller, this is reusable
$dwoo = new Dwoo\Core();
$dwoo->debugMode = false;

// Remove default error  reporting system
/*error_reporting(0);
//(PHP >= 5.1.0 MUST TO DO SO)Sets the default timezone used by all date/time functions in a script. This demo sets the PRC timezone
date_default_timezone_set('Europe/Paris');
//require / require_once Error.php at a proper place as you wish
require_once "Error.php";
//Sets a user function (error_handler & exception_handler) to handle errors in a script
set_exception_handler(array('Inter_Error', 'exception_handler'));
set_error_handler(array('Inter_Error', 'error_handler'), E_ALL);
register_shutdown_function(array('Inter_Error', 'detect_fatal_error'));
//Setting Inter_Error::$conf :

Inter_Error::$conf['debugMode'] = true;*/

/*var_dump(xdebug_dump_superglobals());
var_dump(xdebug_get_declared_vars());
var_dump(xdebug_is_enabled());
var_dump(xdebug_get_function_stack());*/

// Load a template file (name it as you please), this is reusable
// if you want to render multiple times the same template with different data
$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : 'index';

$tpl = new Dwoo\Template\File('tpl/'.$plugin.'.tpl');

// Create a data set, if you don't like this you can directly input an
// associative array in $dwoo->output()
$data = new Dwoo\Data();
// Fill it with some data

// index
$data->assign('foo', 'BAR');
$data->assign('bar', 'BAZ');

// function
$data->assign('menuTree', array(
	array('name'=>'Foo', 'children'=>array(
		array('name'=>'Foo-Sub', 'children'=>array()),
		array('name'=>'Foo-Sub2', 'children'=>array()),
	)),
	array('name'=>'Bar', 'children'=>array()),
	array('name'=>'Baz', 'children'=>array()),
));

// foreach
$data->assign('arr', array(array('id' => 1, 'name' => 'Jim'), array('id' => 2, 'name' => 'John'), array('id' => 3, 'name' => 'Bob')));

$data->assign(array('arrw' => array( 'foo' => 'bar' )));

$data->assign('myObj', $dwoo);

// ... or get it to use it somewhere else
echo $dwoo->get($tpl, $data);