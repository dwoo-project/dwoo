WHAT IS DWOO?
=============
Dwoo is a PHP5/PHP7 Template Engine that was started in early 2008. The idea came
from the fact that Smarty, a well known template engine, is getting older and
older. It carries the weight of it's age, having old features that are
inconsistent compared to newer ones, being written for PHP4 its Object
Oriented aspect doesn't take advantage of PHP5's more advanced features in
the area, etc. Hence Dwoo was born, hoping to provide a more up to date and
stronger engine.

So far it has proven to be faster than Smarty in many areas, and it provides
a compatibility layer to allow developers that have been using Smarty for
years to switch their application over to Dwoo progressively.

> ⚠ Dwoo **1.2.x** is only compatible with **PHP 5.x** ⚠

DOCUMENTATION
=============
Dwoo's website to get the latest version is at http://dwoo.org/   
The wiki/documentation pages are available at http://dwoo.org/documentation/

Requirements
------------
* PHP >= **5.3**
* PHP <= **7.0**
* [Multibyte String](http://php.net/manual/en/book.mbstring.php)

LICENSE
=======
Dwoo is released under the [Modified BSD](./LICENSE) license.
See the LICENSE file included in the archive or go to the URL below to obtain
a copy.

QUICK START - RUNNING DWOO
==========================

Basic Example
-------------
```php
<?php
// Include the main class (it should handle the rest on its own)
require 'vendor/autoload.php';

// Create the controller, this is reusable
$dwoo = new Dwoo();

// Load a template file (name it as you please), this is reusable
// if you want to render multiple times the same template with different data
$tpl = new Dwoo_Template_File('path/to/index.tpl');

// Create a data set, if you don't like this you can directly input an
// associative array in $dwoo->output()
$data = new Dwoo_Data();
// Fill it with some data
$data->assign('foo', 'BAR');
$data->assign('bar', 'BAZ');

// Outputs the result ...
$dwoo->output($tpl, $data);
// ... or get it to use it somewhere else
$dwoo->get($tpl, $data);
```

Loop Example
------------
```php
<?php
// To loop over multiple articles of a blog for instance, if you have a
// template file representing an article, you could do the following :

require 'vendor/autoload.php';

$dwoo = new Dwoo();
$tpl = new Dwoo_Template_File('path/to/article.tpl');

$pageContent = '';

// Loop over articles that have been retrieved from the DB
foreach($articles as $article) {
    // Either associate variables one by one
    $data = new Dwoo_Data();
    $data->assign('title', $article['title']);
    $data->assign('content', $article['content']);
    $pageContent .= $dwoo->get($tpl, $data);

    // Or use the article directly (which is a lot easier in this case)
    $pageContent .= $dwoo->get($tpl, $article);
}
```