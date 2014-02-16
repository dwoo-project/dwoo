<?php
// Include the main class, the rest will be automatically loaded
/*require '../lib/Dwoo/Autoloader.php';
$autoloader = new Dwoo\Autoloader();
$autoloader->add('Dwoo', '../lib/Dwoo');
$autoloader->register(true);*/
require 'phar://../dwoo.phar';

// Create the controller, it is reusable and can render multiple templates
$dwoo = new Dwoo\Core();

// Create some data
$data = array('a'=>5, 'b'=>6);

// Output the result ...
$dwoo->output('tpl/index.tpl', $data);
// ... or get it to use it somewhere else
//echo $dwoo->get('tpl/index.tpl', $data);
 