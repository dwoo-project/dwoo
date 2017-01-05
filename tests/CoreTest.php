<?php

namespace Dwoo\Tests
{

    use Dwoo\Compiler;
    use Dwoo\Core;
    use Dwoo\Plugins\Blocks\PluginTopLevelBlock;
    use Dwoo\Template\Str as TemplateString;
    use Dwoo\Template\File as TemplateFile;
    use Dwoo\Security\Policy as SecurityPolicy;
    use ProxyHelper;
    use stdClass;
    use TestArrayAccess;
    use TestCountableArrayAccess;
    use TestCountableIterator;
    use TestIterator;

    /**
     * Class CoreTest
     *
     * @package Dwoo\Tests
     */
    class CoreTest extends BaseTests
    {

        public function testCoverConstructorsEtc()
        {
            // extend this class and override this in your constructor to test a modded compiler
            $tpl            = new TemplateString('');
            $tpl->forceCompilation();
            $this->assertEquals('', $this->dwoo->get($tpl, array(), $this->compiler));

            // fixes the init call not being called (which is normal)
            $fixCall = new PluginTopLevelBlock($this->dwoo);
            $fixCall->init();
        }

        public function testReadVar()
        {
            $tpl = new TemplateString('{$foo.$bar[$baz->qux][moo]}{with $foo}{$a.b.moo}{/with}{$baz->qux}');
            $tpl->forceCompilation();

            $Obj      = new stdClass();
            $Obj->qux = 'b';
            $data     = array(
                'foo' => array('a' => array('b' => array('moo' => 'Yay!'))),
                'bar' => 'a',
                'baz' => $Obj,
            );
            $this->assertEquals('Yay!Yay!b', $this->dwoo->get($tpl, $data, $this->compiler));

            $this->assertEquals('Yay!aaab', $this->dwoo->readVar('foo.a.b.moo') . $this->dwoo->readVar('bar') . $this->dwoo->readVar('_root.bar') . $this->dwoo->readVar('_parent.bar') . $this->dwoo->readVar('baz->qux'));
            $this->assertEquals($data, $this->dwoo->readVar('_root'));
            $this->assertEquals($data, $this->dwoo->readVar('_parent'));
        }

        public function testReadParentVar()
        {
            $tpl = new TemplateString('{assign "Yay!" a.b->qux}{$a.b->qux}');
            $tpl->forceCompilation();

            $this->assertEquals('Yay!', $this->dwoo->get($tpl, array('bar' => 'a'), $this->compiler));

            $this->assertEquals('a', $this->dwoo->readParentVar(2, 'bar'));
        }

        public function testDwooOutput()
        {
            $tpl = new TemplateString('a');
            $tpl->forceCompilation();

            ob_start();
            echo $this->dwoo->get($tpl, array());
            $output = ob_get_clean();
            $this->assertEquals('a', $output);
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testDwooGetNonTemplate()
        {
            echo $this->dwoo->get(null, array());
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testDwooGetNonData()
        {
            $tpl = new TemplateString('a');
            $this->dwoo->get($tpl, null);
        }

        public function testGetSetSecurityPolicy()
        {
            $policy = new SecurityPolicy();
            $policy->setConstantHandling(SecurityPolicy::CONST_ALLOW);
            $this->dwoo->setSecurityPolicy($policy);
            $this->assertEquals($policy, $this->dwoo->getSecurityPolicy());
            $this->assertEquals($policy->getConstantHandling(), $this->dwoo->getSecurityPolicy()->getConstantHandling());
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testWrongResourceName()
        {
            $this->dwoo->templateFactory('sdmlb', 'fookm');
        }

        public function testIsCached()
        {
            $tpl = new TemplateString('foo');
            $this->assertEquals(false, $this->dwoo->isCached($tpl));
        }

        public function testClearCache()
        {
            $cacheDir = $this->dwoo->getCacheDir();
            $this->dwoo->clearCache();
            file_put_contents($cacheDir . DIRECTORY_SEPARATOR . 'junk.html', 'test');

            $this->assertEquals(1, $this->dwoo->clearCache());
        }

        public function testClearCompiled()
        {
            $compiledDir = $this->dwoo->getCompileDir();
            $this->dwoo->clearCompiled();
            file_put_contents($compiledDir . DIRECTORY_SEPARATOR . 'junk.html', 'test');
            $this->assertEquals(1, $this->dwoo->clearCompiled());
        }

        public function testDwoo_GetFilename()
        {
            $this->assertEquals('44BAR', $this->dwoo->get(__DIR__ . '/resources/test.html', array(
                'foo' => 44,
                'bar' => 'BAR'
            )));
        }

        public function testAssignVarInScope()
        {
            $tpl = new TemplateString('{assign "Yay!" a.b->qux}{$a.b->qux}');
            $tpl->forceCompilation();

            $Obj      = new stdClass();
            $Obj->qux = 'Noes:(';

            $this->assertEquals('Yay!', $this->dwoo->get($tpl, array('a' => array('b' => $Obj)), $this->compiler));

            $tpl = new TemplateString('{assign "Yay!" a->b.qux}{$a->b.qux}');
            $tpl->forceCompilation();

            $Obj    = new stdClass();
            $Obj->b = array('qux' => 'Noes:(');

            $this->assertEquals('Yay!', $this->dwoo->get($tpl, array('a' => $Obj), $this->compiler));
        }

        public function testPhpCall()
        {
            $tpl = new TemplateString('{"foo"|strtoupper}');
            $tpl->forceCompilation();

            $this->assertEquals('FOO', $this->dwoo->get($tpl, array(), $this->compiler));

            $tpl = new TemplateString('{foreach $foo|@count subitems}{$subitems}{/foreach}');
            $tpl->forceCompilation();

            $this->assertEquals('21', $this->dwoo->get($tpl, array(
                'foo' => array(
                    'a' => array(1, 2),
                    'b' => array(2)
                )
            ), $this->compiler));
        }

        public function testClassCall()
        {
            $tpl = new TemplateString('{dump $foo.b.0}');
            $tpl->forceCompilation();

            $this->assertEquals('2<br />', $this->dwoo->get($tpl, array(
                'foo' => array(
                    'a' => array(1, 2),
                    'b' => array(2)
                )
            ), $this->compiler));
        }

        public function testGlobal()
        {
            $this->dwoo->addGlobal('test', 'value');
            $tpl = new TemplateString('{$.test}');
            $this->assertEquals('value', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testSuperGlobals()
        {
            $_GET[5] = 'Yay';
            $tpl     = new TemplateString('{$dwoo.get.5} {$dwoo.get.$foo}');
            $tpl->forceCompilation();

            $this->assertEquals('Yay Yay', $this->dwoo->get($tpl, array('foo' => 5), $this->compiler));
        }

        public function testGettersSetters()
        {
            $this->dwoo->setCacheTime(5);
            $this->assertEquals(5, $this->dwoo->getCacheTime());
            $this->dwoo->setCacheTime(0);

            $this->dwoo->setCharset('foo');
            $this->assertEquals('foo', $this->dwoo->getCharset());
            $this->dwoo->setCharset('utf-8');

            $this->dwoo->setDefaultCompilerFactory('file', 'Moo');
            $this->assertEquals('Moo', $this->dwoo->getDefaultCompilerFactory('file'));
            $this->dwoo->setDefaultCompilerFactory('file', array('Dwoo\Compiler', 'compilerFactory'));
        }

        public function testAddAndRemoveResource()
        {
            $this->dwoo->addResource('news', 'Dwoo\Template\File', array('Dwoo\Compiler', 'compilerFactory'));
            $tpl = new TemplateString('{include file="news:' . __DIR__ . '/resources/test.html" foo=3 bar=4}');
            $tpl->forceCompilation();

            $compiler = new Compiler();
            $this->assertEquals('34', $this->dwoo->get($tpl, array(), $compiler));
            $this->dwoo->removeResource('news');

            $this->dwoo->addResource('file', 'Dwoo\Template\Str', 'Fake');
            $this->dwoo->removeResource('file');
            $tpl = new TemplateString('{include file="file:' . __DIR__ . '/resources/test.html" foo=3 bar=4}');
            $tpl->forceCompilation();
            $this->assertEquals('34', $this->dwoo->get($tpl));
        }

        public function testTemplateFile()
        {
            $tpl = new TemplateFile(__DIR__ . '/resources/test.html');
            $tpl->forceCompilation();

            $this->assertEquals('12', $this->dwoo->get($tpl, array('foo' => 1, 'bar' => 2)));
        }

        public function testTemplateString()
        {
            $tpl = new TemplateString('foo', 13);

            $this->assertEquals('13', $tpl->getCacheTime());
            $this->assertEquals(null, $tpl->getCompiler());
        }

        public function testCachedTemplateAndClearCache()
        {
            $tpl = new TemplateString('foo{$foo}', 10, 'cachetest');
            $tpl->forceCompilation();

            $this->assertEquals('foo1', $this->dwoo->get($tpl, array('foo' => 1)));
            $this->assertEquals(true, $this->dwoo->isCached($tpl));
            $this->assertEquals('foo1', $this->dwoo->get($tpl, array('foo' => 1)));
            $this->assertEquals(1, $this->dwoo->clearCache(- 11));
            $this->assertEquals(false, $this->dwoo->isCached($tpl));
        }

        public function testCachedTemplateAndOutput()
        {
            $tpl = new TemplateString('foo{$foo}', 10, 'cachetest');
            $tpl->forceCompilation();

            ob_start();
            echo $this->dwoo->get($tpl, array('foo' => 1));
            $cap = ob_get_clean();
            $this->assertEquals('foo1', $cap);
            $this->assertEquals(true, $this->dwoo->isCached($tpl));
            ob_start();
            echo $this->dwoo->get($tpl, array('foo' => 1));
            $cap = ob_get_clean();
            $this->assertEquals('foo1', $cap);
            $this->assertEquals(1, $this->dwoo->clearCache(- 11));
        }

        public function testCachedTemplateWithDwoo_Cache()
        {
            $this->dwoo->setCacheTime(10);
            $tpl = new TemplateString('foo{$foo}bar', null, 'cachetest2');
            $tpl->forceCompilation();

            $this->assertEquals('foo1bar', $this->dwoo->get($tpl, array('foo' => 1)));
            $this->assertEquals(true, $this->dwoo->isCached($tpl));
            $this->assertEquals('foo1bar', $this->dwoo->get($tpl, array('foo' => 1)));
            $this->assertEquals(1, $this->dwoo->clearCache(- 11));
            $this->assertEquals(false, $this->dwoo->isCached($tpl));
        }

        public function testClearCacheOnTemplateClass()
        {
            $this->dwoo->setCacheTime(10);
            $tpl = new TemplateString('foo{$foo}bar', null, 'cachetest2');
            $tpl->forceCompilation();

            $this->assertEquals('foo1bar', $this->dwoo->get($tpl, array('foo' => 1)));
            $this->assertEquals(true, $this->dwoo->isCached($tpl));
            $this->assertEquals('foo1bar', $this->dwoo->get($tpl, array('foo' => 1)));
            $this->assertEquals(false, $tpl->clearCache($this->dwoo, 10));
            $this->assertEquals(true, $tpl->clearCache($this->dwoo, - 1));
            $this->assertEquals(false, $this->dwoo->isCached($tpl));
        }

        public function testTemplateGetSet()
        {
            $this->dwoo->setCacheTime(10);
            $tpl  = new TemplateString('foo');
            $tpl2 = new TemplateFile('./resources/test.html');

            $this->assertEquals(false, $tpl->getResourceIdentifier());
            $this->assertEquals('string', $tpl->getResourceName());
            $this->assertEquals('file', $tpl2->getResourceName());
            $this->assertEquals(hash('md4', 'foo'), $tpl->getUid());
        }

        public function testPluginProxyGetSet()
        {
            $proxy = new ProxyHelper();
            $dwoo  = new Core();
            $dwoo->setPluginProxy($proxy);
            $this->assertEquals($proxy, $dwoo->getPluginProxy());
        }

        public function testIsArrayArray()
        {
            $dwoo = new Core();
            $data = array();
            $this->assertEquals(true, $dwoo->isArray($data));
            $this->assertEquals(0, $dwoo->isArray($data, true));
            $data = array('moo');
            $this->assertEquals(true, $dwoo->isArray($data));
            $this->assertEquals(1, $dwoo->isArray($data, true));
        }

        public function testIsArrayArrayAccess()
        {
            $dwoo = new Core();
            $data = new TestArrayAccess(array());
            $this->assertEquals(true, $dwoo->isArray($data));
            $this->assertEquals(0, $dwoo->isArray($data, true));
            $data = new TestArrayAccess(array('moo'));
            $this->assertEquals(true, $dwoo->isArray($data));
            $this->assertEquals(true, $dwoo->isArray($data, true));
        }

        public function testIsArrayCountableArrayAccess()
        {
            $dwoo = new Core();
            $data = new TestCountableArrayAccess(array());
            $this->assertEquals(true, $dwoo->isArray($data));
            $this->assertEquals(0, $dwoo->isArray($data, true));
            $data = new TestCountableArrayAccess(array('moo'));
            $this->assertEquals(true, $dwoo->isArray($data));
            $this->assertEquals(1, $dwoo->isArray($data, true));
        }

        public function testIsTraversableIterator()
        {
            $dwoo = new Core();
            $data = new TestIterator(array());
            $this->assertEquals(true, $dwoo->isTraversable($data));
            $this->assertEquals(0, $dwoo->isTraversable($data, true));
            $data = new TestIterator(array('moo'));
            $this->assertEquals(true, $dwoo->isTraversable($data));
            $this->assertEquals(true, $dwoo->isTraversable($data, true));
        }

        public function testIsTraversableCountableIterator()
        {
            $dwoo = new Core();
            $data = new TestCountableIterator(array());
            $this->assertEquals(true, $dwoo->isTraversable($data));
            $this->assertEquals(0, $dwoo->isTraversable($data, true));
            $data = new TestCountableIterator(array('moo'));
            $this->assertEquals(true, $dwoo->isTraversable($data));
            $this->assertEquals(1, $dwoo->isTraversable($data, true));
        }

        public function testSetters()
        {
            $dwoo = new Core();
            $dwoo->setCacheDir($this->cacheDir);
            $dwoo->setCompileDir($this->compileDir);
            $dwoo->setTemplateDir(__DIR__ . DIRECTORY_SEPARATOR . 'resources');

            $this->assertThat($dwoo->get('extend1.html', array(), $this->compiler), new DwooConstraintStringEquals('foo
child1
toplevelContent1
bar
toplevelContent2
baz'));
        }
    }
}
namespace
{

    class TestIterator implements Iterator
    {
        protected $data;
        protected $idx = 0;

        public function __construct($data)
        {
            $this->data = $data;
        }

        public function current()
        {
            return $this->data[$this->idx];
        }

        public function next()
        {
            ++ $this->idx;
        }

        public function rewind()
        {
            $this->idx = 0;
        }

        public function key()
        {
            return $this->idx;
        }

        public function valid()
        {
            return isset($this->data[$this->idx]);
        }
    }

    class TestCountableIterator extends TestIterator implements Countable
    {
        public function count()
        {
            return count($this->data);
        }
    }

    class TestArrayAccess implements ArrayAccess
    {
        protected $data;

        public function __construct($data)
        {
            $this->data = $data;
        }

        public function offsetExists($k)
        {
            return isset($this->data[$k]);
        }

        public function offsetGet($k)
        {
            return $this->data[$k];
        }

        public function offsetUnset($k)
        {
            unset($this->data[$k]);
        }

        public function offsetSet($k, $v)
        {
            $this->data[$k] = $v;
        }
    }

    class TestCountableArrayAccess extends TestArrayAccess implements Countable
    {
        public function count()
        {
            return count($this->data);
        }
    }
}