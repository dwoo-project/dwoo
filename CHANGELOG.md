## 1.3.6 (2017-03-27)
`Changed`
* In `setCacheDir` and `setCompileDir` methods, create directory path if not exist if possible.
* Rename `Compiler::getDwoo()` to `Compiler::getCore()`.

`Fixed`
* Fix bug, using object for custom plugin and using `getobjectPlugin` method.
* Fix error, `getCore` need to return `$this->core`.
* Fix output data bug in `replaceModifiers` method.
* Fix `addPlugin` method, add code when passing object in `$callback`.

## 1.3.5 (2017-03-16)
`Added`
* Add new constant test from file `testShortClassConstants`.

`Changed`
* Update tests, add new test `testClassConstants`.
* Update `Loader::rebuildClassPathCache` method, using `DirectoryIterator` class instead of `glob` function.

`Fixed`
* Fix class `BaseTests`, remove *cached* and *compiled* files when calling **destructor**.
* Constants error when using 2 trailing slashes, issue [#63](https://github.com/dwoo-project/dwoo/issues/63).
* Fix `parseConstKey` method, replacing double `\\` by single `\`.
* Fix `throw new` exception, display on one line.

`Removed`
* Remove useless `else` and non accessing code.

## 1.3.4 (2017-03-07)
`Added`
* Add `docker-compose.yml` file for unit testing only.

`Changed`
* Update PHPUnit commands in *tests/README.md* file.
* Ignore `composer.lock` file.
* Update code `while (list(, $v) = each($args))` to `foreach ($args as $key => $v)`.

`Fixed`
* Fixing issue [#58](https://github.com/dwoo-project/dwoo/issues/58).
* Update method `Dwoo\Template\File::getCompiledFilename`.

## 1.3.3 (2017-01-09)
`Added`
* Add new parameter `allowed_tags` for **strip_tags** plugin.

`Changed`
* All **function's plugins** as *PHP function* has been converted to *PHP class*, following now PSR-0 and PSR-4 standards.
* `array` helper as *PHP function* has been converted to *PHP class*, following now PSR-0 and PSR-4 standards.

## 1.3.2 (2017-01-05)
`Added`
* Add new tests: `CoreTest::testSetters`, `CoreTest::testGlobal`.
* Add new methods: `Core::setTemplateDir` and `Core::getTemplateDir`.
* Add new alias `js` for *javascript* available format in **escape** plugin.
* Add new methods `Core::addGlobal` and `Core::getGlobals`.

`Changed`
* Properties `Core::$data` and `Core::$globals` are now protected.

`Fixed`
* Fix PHPUnit test: `CompilerTest::testConstants`.

## 1.3.1 (2016-12-16)
* Now fully compatible with PHP7.0 and PHP7.1.

`Changed`
* Rename class `Dwoo\Template\String` to `Dwoo\Template\Str`.

`Fixed`
* Fixing all adapters.
* Fixing constant calls from classes with namespaces.

## 1.3.0 (2016-09-25)
`Added`
* Add namespaces.
* Add new PHPDoc block **Copyright** for each files.

`Changed`
* Follows [PHP Coding Standards Fixer](http://cs.sensiolabs.org/).
* Refactoring code, following [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) Standard.
* Follows PSR-1: Basic Coding Standard.
* Follows PSR-2: Cosing Style Guide.
* Follows PSR-4: Autoloader Standard instead of PSR-0.
* Update **README** examples with namespace.
* Move all plugins from `plugins/builtin` to `Dwoo/Plugins`.
* Processor `smarty_compat` become `PluginSmartyCompatible`.
* All plugins **functions** and **classes** names MUST start with `Plugin` keyword.
* All plugins filename MUST have the same name as the **function** or **class**.
* Plugins name changed from **underscore_case** to **CamelCase** (e.g. `Dwoo_Plugin_assign_compile` is now `PluginAssignCompile`).
* Helper `Dwoo_Plugin_array_compile` move to `Dwoo\Plugins\Helpers\PluginArrayCompile`.

`Removed`
* Delete `Dwoo` class, now you need to use: `new Dwoo\Core()`.
* Delete method `Dwoo\Core::output()`, use now `echo Dwoo\Core::get()` method.
* Last parameter `$_output` of `Dwoo\Core::get()` method has been removed, use `echo Dwoo\Core::get()` instead.

## 1.2.3 (2016-09-15)
`Added`
* Continuously integrated **Travis** config file.
* Add new method `Dwoo_Core::ClearCompiled()` to clear compiled templates.
* Add new PHPDoc `@author` and `@copyright` tags.

`Removed`
* Unreachable statements.

`Fixed`
* PHPUnit config file.
* PHPUnit tests classes.
* Fix `Dwoo_Core::clearCache()`, remove all files even `.html`.
* Fix all PHPDoc.
* Fix return statements in correlation with PHPDoc.

`Deprecated`
* Method `Dwoo_Core::output()` is now deprecated, will be removed in **1.3.0**.

## 1.2.2 (2016-09-10)
`Added`
* Add support for `XOR` and `^` operators.
* Add support for closure when adding a new plugin with: `$dwoo->addPlugin()`.

`Removed`
* File `lib/Dwoo.compiled.php` has been removed.
* File `lib/dwooAutoload.php` has been removed, use **Composer** to autoload classes.

## 1.2.1 (2014-04-20)
`Added`
* Support for Composer, adding `composer.json` file.

`Changed`
* Follows PSR-0: Autoloader Standard.
* Minimum PHP version required is now **PHP 5.3**

`Deprecated`
* Dwoo class is now deprecated, will be removed in **1.3.0**.

`Fixed`
* Fix for reading `unassigned _filter` from `Zend_View_Abstract` for **ZendFramework** adapter.
* Fixed `{capture}` contents lacking the output of block plugins when multiple block plugins are used.
* Fixed Dwoo compiler to not unnecessarily fail at statements involving method calls and multiple arguments.
* Fixed use of multiple modifiers in combination with method calls.
* Fixed compiler to work with modifiers on method calls in named parameters.
* Fixed stripping of newlines at the very beginning of block tags.
* Fixed compiler to support method calls in delimited strings.
* Fix opcache and apc compatibility.

`Removed`
* Remove include so autoloader can handle it.

## 1.2.0 (2013-02-15)
`Deprecated`
* BC Break: Dwoo_Core::isArray had to be fixed and it has been split up
  in 3 methods, isArray (for array access), isTraversable (for foreach)
  and count (just a helper that counts anything). It won't affect you
  unless you built some plugin depending on isArray, in which case you
  should check all works fine still
* BC Break: The fix for objects implemented __get but not __isset that was
  applied in 1.1.1 was causing problems for people implementing it
  correctly, therefore it had to be reverted. If you are affected you must
  implement __isset() properly
* BC Break: Dwoo_Core::get now returns a new Dwoo_Core instance if it's
  evaluating a template already, this only affects you if you built your
  own include-like plugin, see the changes to include plugin in revision
  346 [http://bugs.dwoo.org/dwoo/browse_code/revision/345] for more infos
  
`Added`
* Added support for nested blocks for template inheritance, block names
  must be unique, overriding of any block is done at the top level, thanks
  to Ian Carpenter for the patch
* Added {return} plugin that allows any included template to return
  variables into the one that included it, or to the main controller
  code via $dwoo->getReturnValues()
* Added support for static vars and methods access on namespaced classes
* Moved Dwoo code to Dwoo_Core that is extended by Dwoo, so you can use
  the Dwoo directory as an svn:externals without problems now and just
  use Dwoo_Core in place of Dwoo in your code
* Improved parsing of array() to support real php array syntax as well
  as variables as array keys, thanks to acecream for the help
* Improved parsing of named parameters that can now be quoted
* Added support for instance and static method calls white-listing in
  Dwoo_Security_Policy (see allowMethod()), this is hardly efficient
  though for instance calls since it has to do runtime checks so use
  it with caution

`Fixed`
* Fixed PHP parse errors being generated in compiled templates when
  {dynamic} was nested
* Fixed extends bug when calling {extends} with parenthesis
* Fixed a double escaping bug when a variable was assigned to another one
* Added $this->viewParam support to ZendFramework adapter through a
  Dwoo_Adapters_ZendFramework_Dwoo class that extends Dwoo, you should use
  this if you called setEngine() on the ZF view
* Fixed parsing of blocks with a huge (25K+) content
* Fixed parsing of quoted keywords in if statements, like 'not' was
  parsed as ! because using {if not $foo} is valid, but it was impossible
  to use them even as string.
* Fixed parsing bug with method calls used as arguments with a comma
  following.
* Fixed parsing of function/constants that start with an underscore,
  thanks to Dominik del Bondio for the patch
* Adapters: Agavi: Added support for multiple plugin directories in the
  config, thanks to Mike Seth for the patch
* Fixed endless loop when parsing an invalid else block
* Fixed custom compilable plugins not being recognized as such

##  1.1.1 (2010-02-07)
`Added`
* Added {optional} plugin that just prints an optional var without any
  notice if it doesn't exist
* Added Dwoo::setTemplate() for testing purposes mostly

`Fixed`
* Fixed an {extends} parsing bug that prevented the use of single-quotes
  around the parent template's filename
* Fixed a security issue, if you didn't use a custom compiler factory but
  passed the compiler directly to the get method with autoEscape enabled,
  the autoEscape was disabled in included templates - Thanks to Fabien
  Potencier for notifying me.
* Fixed a bug in {safe} when using variable-variables it would sometimes
  corrupt the var name resulting in blank output
* Fixed a bug when accessing array indices that contain a minus sign, it
  is now possible to access those using {$var[index-foo]},
  {$var['index-foo']} or {$index="index-foo"} {$var[$index]}
* Fixed a bug in {tif} that didn't work when 0 was given as the true or
  false value
* Fixed a bug when using the autoEscape feature with sub-templates (the
  compiled sub-template couldn't access the dwoo charset property,
  resulting in a fatal error)
* Fixed a property reading bug on objects that implemented __get but not
  __isset, implementing __isset is however very much recommended
* Fixed a Dwoo_Data bug in the append method when the index didn't exist
  yet it threw a notice
* Fixed a bug when accessing global vars from a sub-template
* Fixed a couple bugs in the {dynamic} plugin with regard to using plugins
  within a dynamic block
* Fixed a compilation bug when using a PluginProxy with highly nested calls
* Fixed a {load_templates} bug, plugins used in external templates were not
  loaded correctly, same for custom user plugins
* Cached templates now check the source template for modification before
  outputting the cached version

`Removed`
* Removed a couple of `@` operator calls to `file_get_contents`

## 1.1.0 (2009-07-18)
`Deprecated`
* BC Break: Dwoo::initGlobals() is only called once during the Dwoo object
  construction. If you had overriden it and need to update global data
  before each template is executed you should instead override
  Dwoo::initRuntimeVars() and push stuff in the globals array there. Also
  be aware that this means captured data, foreach values and so-on will
  persist from a parent template to an included one (but the include's
  changes will not be reflected on the parent), and from a template
  to the next if you render several in series.
  
`Added`
* Added {template} plugin that allows you to define sub-templates and then
  call them (works recursively too, good for menus and lists)
* Added {load_templates} to load external sub-templates into your file
* Allowed string concatenation assignments with {$foo.="bar"}
* Allowed access of static properties as {Foo::$bar}
* Plugins/Helpers that use a dynamic number of arguments through
  func_get_args are now working since the compiler lets any arguments in
  excess pass through
* Adapters: CodeIgniter: the adapter by Stefan Verstege has been added to
  core and he will be its maintainer from now on
* Adapters: CakePHP: this adapter is now added to core and is designed to
  work with CakePHP 1.2

`Fixed`
* Adapters: Zend: Denis Arh is now appointed maintainer of that part and
  fixed a few things since 1.0.1
* The include_path isn't altered anymore, hopefully saving some stat calls
* User classes extending Dwoo_Template_File are now supported better with
  regard to includes - Thanks to the Kayako.com team for the patch
* Objects now act like arrays when you access non-existing properties on
  them (i.e. it outputs a notice only if it's output straight, and none
  when passed to a function)
* For can now iterate backwards if you input numbers, it won't work with
  variables though
* Slight performance improvement with big inheritance trees
* No more double-slashes in template paths since this seemed to cause
  slight performance issues
* Fixed a bug with parsing AND/OR keywords in conditionals when they were
  followed by round brackets
* Fixed assignments not handling multi-line values correctly
* Fixed parameter parsing issue when a plugin name was all uppercased
* Fixed assignments failing with autoEscape enabled
* Fixed parsing of vars with string keys that was too greedy
* Fixed an optimization causing foreach/for/loop variables not being
  accessible when the foreach/.. name was set dynamically
* Fixed parsing of comments that were on top of the file when there are
  spaces at the end of it
* Dwoo_Template::$chmod is now enforced for directories as well (#18)
* Many new unit tests to improve code coverage and a bunch of bug fixes
  that resulted, but I didn't really keep track of them

## 1.0.1 (2008-12-24)
`Fixed`
* Direct assignments like {$foo = 5} now allow spaces around the operator
* Fixed a {foreach} bug with the implode argument
* Fixed modulo operator in if statements
* Fixed date_format handling of negative and small unix timestamps
* Fixed a weird reference bug with ZF and includes.. whatever but thanks
  to Denis Arh for the patch

## 1.0.0 (2008-10-23)
`Deprecated`
* BC Break: Small one that probably won't affect anyone, but it makes the
  PluginProxy feature much stronger. Basically if you used a custom one you
  will get a fatal error and need to update it to conform to the new
  IPluginProxy interface, that's it
  
`Added`
* Compiler: the modifier syntax (|foo) can now be applied on functions and on
  complex variables i.e. {$obj->getStuff()|upper} or {lower('foo')|upper}
* SmartyCompat: Added a {section} plugin but I strongly discourage using it,
  it was really made to support legacy templates, since {for} doesn't have to
  handle {section}-BC anymore, it has been cleaned up a lot and the last
  $skip parameter has been dropped
  
`Fixed`
* The core Dwoo class doesn't need writable compile/cache dirs in the
  constructor anymore so you can provide custom ones later through
  ->setCompile(/Cache)Dir - thanks to Denis Arh for the patch
* Adapters: Zend: major overhaul thanks to Denis Arh, templates files should
  probably be moved in the scripts subfolder after this update though, and
  the settings array has changed a bit, you will get warnings if you don't
  update the code anyway
* Plugins: improved the dump plugin, it now displays object's properties
  and optionally public methods (if the new show_methods arg is set to true)
  - thanks to Stephan Wentz for the patch
* Adapters: Zend: Added parameters to provide a custom engine (extends Dwoo)
  or a custom data class (extends Dwoo_Data) - thanks to Maxime Merian for
  the patch
* Compiler: added Dwoo_Compiler->setNestedCommentsHandling(true) to enable
  parsing of nested comments (i.e. {* {* *} *} becomes a valid comment, useful
  to comment out big chunks of code containing comments)
* Lines containing only comments and whitespace are now entirely removed
* Removed comments do not mess up the line count anymore (for error messages)
* Fixed parsing bug in {func()->propertyOfReturnedObject}
* Fixed file template class reading from the string compiler factory - thanks
  to MrOxiMoron for the patch
* Fixed handling of variable variables that contained non standard characters
* Fixed a 1.0.0beta regression that messed with custom plugin directories
  on Windows
* SmartyCompat: Fixed a few bugs in the adapter and processor - thanks to
  Stefan Moonen for the patches

## 1.0.0beta (2008-09-08)
`Deprecated`
* Important note : Dwoo.php should not be included directly anymore, please
  read the UPGRADE_NOTES file for more infos on the matter, if you don't
  your Dwoo install will most likely break after the update anyway
* BC Break: {include} and {extends} now support the include path properly,
  which means that if you include "foo/bar.html" from _any_ template and you
  have an include path set on your template object, it will look in all those
  paths for foo/bar.html. If you use relative paths, for example
  if you include "../foo/bar.html" AND have an include path set, you will now
  have a problem, because you can't mix both approaches, otherwise you should
  be fine, so to fix this you should convert your relative includes/extends
  
`Added`
* Adapters: Added the Agavi interface for Dwoo
  (see /Dwoo/Adapters/Agavi/README)
* API: Added Dwoo_Compilation_Exception methods getCompiler() and
  getTemplate() so you can catch the exception and use those to build a nicer
  error view with all the details you want
* Plugins: Added a mode parameter to {strip} to allow stripping of javascript
  code blocks that use "// comments", because without this special mode the
  comments result in syntax errors
  
`Fixed`
* The Compiler now ensures that a template starting with <?xml will not
  conflict with php using the short_open_tag=On setting
* Complex arrays keys can be read using {$var["Long|Key*With.some)Crap"]},
  however since it is really bad practice I won't spend time fixing edge
  cases, which are $ and '/" characters inside the string. Those will break
  it and that's it.. if you really care feel free to send a patch
* Dwoo->get() is now stricter as to what it accepts as a "template", only
  Dwoo_ITemplate objects or valid filenames are accepted
* Foreach and other similar plugins that support "else" now only count()
  their input before processing when an else block follows
* Various optimizations
* Fixed compiler bug that created a parse error when you had comments in an
  extended template
* Fixed extends bug when extending files in other directories using relative
  paths
* Fixed parsing bug with "|modifier:param|modifier2} with:colon after it"
* Bug fixed with smarty functions called with no parameters (in compat mode)
* Fixed Dwoo::isArray() check, objects implementing ArrayAccess are now
  valid (thanks to Daniel Cousineau for the patch)
* Fixed compiler warning when doing {func()->method()} or {func()->property}
* Fixed compiled/cached files being written in the wrong place when the path
  to the template contains "../"s
* Fixed {if} failing with conditions using upper case operators (i.e. AND)

## 0.9.3 (2008-08-03)
`Added`
* Adapters: Added the ZendFramework interface for Dwoo
  (see /Dwoo/Adapters/ZendFramework/README)
* Plugins: Added the {a} block plugin to generate <a> tags
* Syntax: Added the ";" token that allows to group multiple instructions in one
  single template tag, example: {if $foo; "> $foo";$bar;/} is equal to:
  {if $foo}> {$foo}{$bar}{/} - This also allow block operations such as:
  {a http://url.com; "Text" /} which equals to {a http://url.com}Text{/}
* Syntax: Block plugins that you want to call without content can be
  self-closed just like XML tags (e.g. {a "http://url.com" /} ). Be careful not
  to close a non-block plugin like that however, since it will close it's
  parent block
* Syntax: Static methods can be called using {Class::method()}
* Syntax: It is now possible to use a plugin's result as an object and call
  a method or read a property from it, i.e. {fetchObject()->doStuff()}
* API: Added Dwoo_Plugin::paramsToAttributes() utility function to help
  with the creation of compilable xml/html-related plugins
* API: Added Dwoo->setPluginProxy() and Dwoo_IPluginProxy that allow you to
  hook into the compiler's plugin subsystem to provide your own plugin calls.
  Thanks to Denis Arh for the patch
   => http://forum.dwoo.org/viewtopic.php?id=70
* API: Dwoo->addPlugin() has a third parameter to mark a plugin as compilable
* Compiler supports method calls into a method call's parameters

`Fixed`
* Dwoo_Compiler::implode_r is now public/static so it can be used in other
  places such as plugin proxies
* Syntax: Math expressions in strings are now only allowed when the entire
  expression is delimited, e.g. {"/$foo/$bar"} evaluates as just that while
  {"/`$foo/$bar`"} will result in "/".($foo/$bar), therefore processing the /
  as a division, this is better since URLs using / are far more common than
  math in strings
   => http://forum.dwoo.org/viewtopic.php?id=50
* Compiler now allows the use of the right delimiter inside strings (e.g. {"}"})
* Fixed a bug preventing if blocks containing a {elseif} followed by {else}
* Fixed the Dwoo_ILoader interface and implemented it in Dwoo_Loader now
   => http://forum.dwoo.org/viewtopic.php?id=70
* Fixed a compileId auto-generation creating conflicts
* Include allows paths going in upper levels now such as : "../foo.html"
* Some compiler fixes regarding custom plugins

## 0.9.2 (2008-06-28)
`Deprecated`
* BC Break: Renamed the {strip} modifier/function to {whitespace}, this does
  not affect the strip block, that has been moved off the compiler into a
  plugin. Which is why the name conflict had to be resolved. Please report
  any issue you might encounter when using the strip block
* BC Break: Changed the function signature of Dwoo_Block_Plugin::postProcessing
  it only affects you if you had any custom block plugins, see UPGRADE_NOTES
  for more details
* BC Break: Dwoo_ITemplate::cache() must now return the cached file name or
  false if caching failed, only affects you if you had a custom template class
  and implemented cache() yourself
* BC Break: Dwoo_Loader is not static anymore so anything you did on it directly
  will break. Use $dwoo->getLoader()->addDirectory() instead of
  Dwoo_Loader::addDirectory() for example
* BC Break: DWOO_COMPILE_DIRECTORY and DWOO_CACHE_DIRECTORY are gone, set those
  paths in Dwoo's constructor (i.e. new Dwoo('compiledir', 'cachedir')) if you
  need to override the default ones
  
`Added`
* Plugins: Added {dynamic} that allows cached templates to have dynamic
  (non-cached) parts, when rendering a cached page, the dynamic parts can still
  use the variables you provides
* Plugins: Added {tif} that acts as a ternary if / allows you to use a ternary
  operator inside it
* API: Added a Dwoo_ILoader interface if you want to provide a custom plugin
  loading solution (use $dwoo->setLoader($myLoader))
* Added line numbers in compilation errors and improved several error messages
* Added magic object-access methods to Dwoo_Data, so you can assign values by
  doing $data->var = $val; instead of $data->assign('var', $val);
* Added get()/unassign()/isAssigned() methods to read, remove and check for the
  presence of a var inside a Dwoo_Data object
  
`Fixed`
* Plugins: added a fifth 'string $implode' parameter to {foreach}, it prints
  whatever you provide it between each item of the foreach, just like implode()
* Plugins: added a fourth 'bool $case_sensitive' parameter to {replace}
* Plugins: added a fourth 'bool $trim' parameter to {capture} that trims
  the captured text
* Made the dependency on the hash extension optional
* Fixed compiler bug that prevented method calls combined with named parameters
* Fixed compiler bug that prevented the % shortcut for constants to work within
  function calls (basically it only worked as {%CONST})
* Fixed compiler bug that prevented empty() to be called
* Fixed several modifier parsing bugs
   => http://forum.dwoo.org/viewtopic.php?id=27
* Fixed empty string parsing in modifier applied to variables
* Fixed compiler handling of <?php echo "foo" ?>{template_tag} where there was
  no ';' at the end of the php tag
* Allowed method calls to work with named parameters
* Removed checks for methods/properties being present on objects before calling
  them since these can be handled by __get() and __call()
   => http://forum.dwoo.org/viewtopic.php?id=22
* Calling {func (params)} (with the space between function and params) is now
  allowed
   => http://forum.dwoo.org/viewtopic.php?id=21
* The compiler now allows \r, \n and \t to be parameter splitters as well as
  "," and " ". You can therefore split complex function calls on multiple lines
* Converted most of the code to follow PEAR Coding Standards, hopefully this
  didn't break anything that the tests missed
* A few other minor or internal changes

## 0.9.1 (2008-05-30)
`Added`
+ API: Added Dwoo_Compiler->setAutoEscape() and getAutoEscape() to modify the
  automatic html entity escaping setting. This is disabled by default, and when
  enabled can be overriden with the {safe $var} plugin or the
  {auto_escape disable} block plugin. The block plugin can also be used to
  enable this mode from within a template
+ Syntax: Mixing named and unnamed parameters is now allowed, as long as the
  unnamed ones appear first
+ Syntax: Added {/} shortcut that closes the last opened block

`Fixed`
* Optimized scope-handling functions, {loop} and {with} are now slightly faster
* Fixed a bug in {date_format} that prevented anything but unix timestamps to
  work
* {literal} and {strip} now follow the LooseOpeningsHandling setting
* Fixed complex variables (i.e. {$_root[$c[$x.0]].0}) parsing bugs
* Fixed $dwoo->addResource() breaking if the resource class was not loaded yet,
  autoload should now be called (thanks mike)
* Fixed a block stack bug that messed up {textformat} and possibly usermade
  block plugins

## 0.9.0 (2008-05-10)
`Deprecated`
* BC Break: changed all class names to be PEAR compliant (aka use underscores
  to separate words/paths), sorry about that but I better do it now than in
  six months
* BC Break: $dwoo->output() and get() have been swapped internally, but it
  doesn't change anything for you unless you called output(*, *, *, true)
  directly to emulate get(). This was done to reduce some overhead
* BC Break: $dwoo->getTemplate() changed to $dwoo->templateFactory() and
  $dwoo->getCurrentTemplate() changed to $dwoo->getTemplate() for consistency
  among all classes and factory functions
  
`Added`
+ Added a compiled version of Dwoo that loads faster (especially with opcode
  caches such as APC), include Dwoo.compiled.php instead of Dwoo.php on
  production but if you want to file a bug use Dwoo.php please as it allows
  you to get the proper file/line number where an error occurs. Do not remove
  all other files however since they are not all included in the compiled
  package
+ Plugins: Added {extends} and {block} to handle template inheritance, read
  more about it at http://wiki.dwoo.org/index.php/TemplateInheritance
+ Plugins: Added {loop} that combines {foreach} and {with}, see
  http://wiki.dwoo.org/index.php/Block:loop for details
+ Plugins: Added {do} that executes whatever you feed it whitout echoing the
  result, used internally for extends but you can use it if required
+ Plugins: Added {eol} that prints an end of line character (OS-specific)
+ Syntax: Added shortcut for {$dwoo.const.*} using '%', for example you can use
  {%FOO} instead of {$dwoo.const.FOO}
+ Syntax: When using named parameters, typing a parameter name without any
  value is the same as typing param=true, for example {foo name="test" bar} and
  {foo name="test" bar=true} are equals, can be useful for very complex plugins
  with huge amounts of parameters.
+ Syntax: Added support for {$foo+=5}, {$foo="a"}, {$foo++} and {$foo--}
+ Syntax: Added shortcut for $dwoo.*, you can now use {$.foreach.foo} instead
  of {$dwoo.foreach.foo} for example, applies to all $dwoo.* vars
+ Syntax: Added $ as a shortcut for current scope, $_ for $_parent and $__ for
  $_root
+ API: Added getSource(), getUid() and getResourceIdentifier() to Dwoo_ITemplate
+ API: Added setSecurityPolicy() too Dwoo_ICompiler and modified the arguments
  of its compile() method
+ API: Added a bunch of utility functions to Dwoo_Compiler, allowing compiled
  plugins to access more of the compiler internals
+ Both cache and compile IDs can now have slashes in them to create subfolders
  in the cache/compile dirs
+ Added a DWOO_CHMOD constant that, if set before you include Dwoo, allows you
  to define the file mode of all the file/directories Dwoo will write, defaults
  to 0777
+ Added a 'data' argument to {include} to be able to feed data directly into it

`Fixed`
* The compiler now throws Dwoo_Compilation_Exception exceptions upon failure
  and security problems lead to a Dwoo_Security_Exception being thrown. Runtime
  plugin errors and such trigger simple php errors to allow the template
  execution to complete
* Fixed a potential concurrency issue (thanks to Rasmus Schultz for the patch)
* Moved all files to Dwoo/Class.php excepted for the core Dwoo.php file
* Various performance improvements, including the removal of a lot of isset()
  calls. Doing {$foo} if foo is undefined will now display a PHP warning, but
  doing {foreach $foo}..{/foreach} will not however, that way you don't have
  to do {if isset($foo)} before the foreach, but automated isset() calls don't
  impact performance as much as they did before.
* API: Dwoo_ITemplate->clearCache now requires a Dwoo instance as its first arg,
  should not affect you unless you built a custom template class from scratch
* Reworked Dwoo template rendering to avoid variable conflicts with plugins
* {include} now uses the current resource if none is provided instead of using
  file as it did before
* Dwoo uses include path instead of absolute includes
* Changed all line endings to Unix (line feed only) and all spaces left have
  been converted to tabs (tabspace 4)
* TestFest happened early for Dwoo, lots of new tests and more code covered
* Fixed a regression in the handling of custom class plugins
* Fixed various bugs in the Adapter class and related smarty compatibility
  features
* Fixed a classpath rebuilding bug that occured on some UNIX platforms due to
  glob() returning false sometimes for empty folders
* Fixed a bug in Dwoo_Security_Policy->getAllowedDirectories(), no security
  issue though
* Fixed a bug in Dwoo::setScope affecting {loop} and {with}
* Fixed a parsing bug when doing {"string"|modifier:$var}

## 0.3.4 (2008-04-09)
`Deprecated`
* BC Break: DWOO_PATH constant changed to DWOO_DIRECTORY
* BC Break: Smarty's @ operator for modifiers is now reversed, for example
  $array|reverse will reverse the items of that array while $array|@reverse
  will reverse each item of the given array (as if you used array_map)
  
`Added`
+ Syntax: Added support for method calls on objects i.e. {$foo->bar()}
+ Added support for smarty security features, see the DwooSecurityPolicy class
  and $dwoo->setSecurityPolicy()
+ API: Added a DwooCompiler->setLooseOpeningHandling() method that, if set to
  true, allows tags to contain spaces between the opening bracket and the
  content. Turned off by default as it allows to compile files containing
  css and javascript without the need to escape it through {literal} or \{
+ Added DWOO_CACHE_DIRECTORY and DWOO_COMPILE_DIRECTORY constants that you can
  set before including Dwoo.php to override the defaults (although
  Dwoo->setCacheDir/setCompileDir() still work to change that if required)
+ Added the DwooException class
+ Smarty: Added partial support for register_object(), unregister_object() and
  get_registered_object(). All features can not be supported by the adapter
  though so you might get compatibility warnings
  
`Fixed`
* Fixed {elseif} bug that appeared when multiple elseif tags were used in a row
* Syntax: Improved simple math support to work within variable variables
  (i.e. you can do {$array[$index+1]}) and within strings as well. To prevent
  this enclose the variables in backticks (i.e. {"$foo/$bar"} will do the math
  while {"`$foo`/$bar"} won't as $foo is properly delimited)
* Changed DwooLoader::addDirectory() so that it writes the class paths cache
  into DWOO_COMPILE_DIRECTORY, that way you don't have to make your plugin
  directory writable
* Made all the error triggering more consistent
* Changed automatic cacheId generation in DwooTemplateFile/String to be faster

## 0.3.3 (2008-03-19)
`Added`
+ Syntax: Added support for $dwoo.const.CONSTANT and
  $dwoo.const.Class::CONSTANT to read PHP constants from the template
+ Syntax: Added support for on/off/yes/no, that work as aliases for true/false
+ Syntax: Added the $dwoo.charset global variable
+ Plugins: Added {withelse} and made {with} compatible with {else} also
+ API: Added left/right delimiters customization, see DwooCompiler->setDelimiters()
+ API: Added DwooCompiler->triggerError()
+ API: Added Dwoo->clearCache() and DwooITemplate->clearCache() methods
+ Smarty: The smartyCompat prefilter converts {section} tags into {for} tags on the
  fly, however it's not guaranteed to work with *all* section tags, let me know if
  it breaks for you
  
`Fixed`
* {with} now skips the entire block if it's variable doesn't exist, so by
  itself it acts as if you would do {if $var}{with $var}{/with}{/if}
* Each resource has a compiler factory function assigned to it, allowing you to
  easily provide a custom compiler without loading it on every page
* OutputFilters are now simply called Filters (they still use DwooFilter)
* Pre/PostFilters have become Pre/PostProcessors (they now use DwooProcessor)
* Compiler: Fixed parsing bug that prevented function names of 1character
* Compiler: Changed internal handling of variables to fix some errors being
  thrown with specific cases
* Reorganized Dwoo/DwooCompiler and fully commented all the core classes
  and interfaces

## 0.3.2 (2008-03-09)
`Added`
+ Added access to superglobals through $dwoo.get.value, $dwoo.post.value,
  etc.
+ Added outputFilters to Dwoo (use Dwoo->addOutputFilter and
  Dwoo->removeOutputFilter)
+ Added preFilters and postFilters to DwooCompiler (use
  DwooCompiler->addPreFilter, etc)
+ Added a html_format output filter that intends properly the html code,
  use it only on full page templates
+ Plugins: Added {for} and {forelse} which allow to loop over an array or to
  loop over a range of numbers
+ Plugins: Added {mailto}, {counter}, {eval}, {fetch} and {include}
+ Syntax : Enhanced support for implicit math operations,
  {$var+$var2*var3+5} now works. Operations are executed from left to right
  though, there is no operator priority. (i.e. 1+1*2 = (1+1)*2 = 4, not 3)
+ API: Added resources support through DwooITemplate implementations and
  Dwoo->addResource()
+ API: Added Dwoo->getTemplate() to get the currently running template object
+ API: Added DwooCompiler::getInstance() to use only one compiler object when
  rendering from the default compiler and to provide you with a singleton if
  it's easier, however the class is not a singleton in the sense that it can
  be instantiated separately
+ API: Added a factory method on DwooITemplate to support resources creation
+ Added a release tag so that all compiled templates are forced to recompile
  after an update, however it is recommended to cleanup your "compiled"
  directory now and then as each release uses new filenames
+ Added an abstract DwooFilter class that you can extend to build filters

`Fixed`
* PHP function calls are now case insensitive
* Syntax: The compiler now parses expressions before modifiers, allowing for
  {$var/2|number_format} for example
* DwooTemplateFile now extends DwooTemplateString instead of the other way
  around as it was before
* {else} is now a general purpose plugin that can act as 'else' for foreach,
  for and if/elseif, foreachelse is still available though

## 0.3.1 (2008-03-05)
`Added`
+ Added {cycle} function
+ Syntax : Enabled support for associative arrays using
  array(key="value", key2=5) for example, which you can assign or use in a
  foreach directly
+ Syntax : Added support for {$var +-/*% X} (i.e. {$var + 4}), useful for
  simple math operations without the math plugin
+ API : Added append/appendByRef to DwooData
+ Completely rebuilt DwooSmartyAdapter, it should "work" and fail silently if
  you use a non supported function now, however you can set
  $smarty->show_compat_errors=true; on it to receive notices about unsupported
  features that you use
  
`Fixed`
* Bug fixed in {literal} parsing
* Bug fixed in smarty functions handling
* API : Moved Plugin types constants to Dwoo so the compiler doesn't have to
  be loaded unles really required
* API : Moved globals and var reinitialization in Dwoo into their own methods
  so that child classes can easily add globals
* Some improvements in the compiler output
* Some changes in the cache handling of DwooTemplateFile

`Removed`
- Special thanks to Andrew Collins that found many of the bugs fixed in this
  release

## 0.3.0 (2008-03-02)
`Added`
+ Full template cache support
+ DwooTemplateString class to load templates from a string
+ Dwoo::VERSION constant
+ {dump} plugin to print out variables
+ Unit tests (with PHPUnit) covering 73% of the codebase right now, which
  should help reducing regression bugs in the next versions.
+ Started commenting (with phpdocs) properly all the classes, should be
  complete for 0.4.0
  
`Fixed`
* {capture} is now compilable and has a new boolean flag to append output into
  the target variable instead of overwriting
* {foreach} supports direct input (instead of only variables), allowing
  constructs like {foreach array(a,b,c) val}{$val}{/foreach} for example that
  would output abc.
* pre/postProcessing functions in block plugins now receive an array of named
  parameters instead of numbered
* Major refactoring of DwooTemplateFile and DwooCompiler
* Cleaned up members visibility in Dwoo/DwooCompiler
* Fixes in the compiler parsing and general variables handling
* Multiple bugfixes here and there thanks to the unit tests
* Optimized {foreach} a lot

## 0.2.1 (2008-02-19)
`Fixed`
* Compiler fixes for argument parsing and handling of Smarty plugins

## 0.2.0 (2008-02-14)
`Added`
+ Added support for plugins made for Smarty (that includes modifiers,
  functions and blocks). Not thoroughly tested.
+ Major API changes in the way Dwoo must be run, it's now much more
  flexible and should not change too much in the future.
+ Added support for custom plugins, filters should come in the next version
  although the API to register them is already in.

## 0.1.0 (2008-02-08)
* Initial release