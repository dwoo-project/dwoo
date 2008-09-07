<?php

function dwooAutoload($class)
{
	if (substr($class, 0, 5) === 'Dwoo_') {
		include strtr($class, '_', DIRECTORY_SEPARATOR).'.php';
	}
}

spl_autoload_register('dwooAutoload');

set_include_path(str_replace(PATH_SEPARATOR.dirname(__FILE__), '', get_include_path()) . PATH_SEPARATOR . dirname(__FILE__));

include 'Dwoo.php';