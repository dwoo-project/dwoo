<?php

namespace Dwoo\Tests
{

    use Dwoo\Template\Str as TemplateString;
    use Dwoo\Security\Policy as SecurityPolicy;
    use PHPUnit_Framework_Error;
    use testSecurityClass;

    /**
     * Class SecurityTest
     *
     * @package Dwoo\Tests
     */
    class SecurityTest extends BaseTests
    {
        protected $policy;

        public function __construct($name = null, array $data = array(), $dataName = '')
        {
            parent::__construct($name, $data, $dataName);

            $this->policy = new SecurityPolicy();
            $this->dwoo->setSecurityPolicy($this->policy);
        }

        public function testConstantHandling()
        {
            $tpl = new TemplateString('{$dwoo.const.PHP_VERSION}');
            $tpl->forceCompilation();

            $this->assertEquals('', $this->dwoo->get($tpl, array(), $this->compiler));

            $this->policy->setConstantHandling(SecurityPolicy::CONST_ALLOW);

            $tpl = new TemplateString('{$dwoo.const.PHP_VERSION}');
            $tpl->forceCompilation();

            $this->assertEquals(PHP_VERSION, $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testPhpHandling()
        {
            $this->policy->setPhpHandling(SecurityPolicy::PHP_ALLOW);

            $tpl = new TemplateString('<?php echo "moo"; ?>');
            $tpl->forceCompilation();

            $this->assertEquals('moo', $this->dwoo->get($tpl, array(), $this->compiler));

            $this->policy->setPhpHandling(SecurityPolicy::PHP_ENCODE);

            $tpl = new TemplateString('<?php echo "moo"; ?>');
            $tpl->forceCompilation();

            $this->assertEquals(htmlspecialchars('<?php echo "moo"; ?>'), $this->dwoo->get($tpl, array(), $this->compiler));

            $this->policy->setPhpHandling(SecurityPolicy::PHP_REMOVE);

            $tpl = new TemplateString('<?php echo "moo"; ?>');
            $tpl->forceCompilation();

            $this->assertEquals('', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testAllowPhpFunction()
        {
            $this->policy->allowPhpFunction('testphpfunc');

            $tpl = new TemplateString('{testphpfunc("foo")}');
            $tpl->forceCompilation();

            $this->assertEquals('fooOK', $this->dwoo->get($tpl, array(), $this->compiler));

            $this->policy->disallowPhpFunction('testphpfunc');
        }

        /**
         * @expectedException \Dwoo\Security\Exception
         */
        public function testNotAllowedPhpFunction()
        {
            $tpl = new TemplateString('{testphpfunc("foo")}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        public function testAllowMethod()
        {
            $this->policy->allowMethod('testSecurityClass', 'testOK');

            $tpl = new TemplateString('{$obj->testOK("foo")}');
            $tpl->forceCompilation();

            $this->assertEquals('fooOK', $this->dwoo->get($tpl, array('obj' => new testSecurityClass()), $this->compiler));

            $this->policy->disallowMethod('testSecurityClass', 'test');
        }

        /**
         * @expectedException PHPUnit_Framework_Error
         */
        public function testNotAllowedMethod()
        {
            $tpl = new TemplateString('{$obj->testOK("foo")}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array('obj' => new testSecurityClass()), $this->compiler);
        }

        public function testAllowStaticMethod()
        {
            $this->policy->allowMethod('testSecurityClass', 'testStatic');

            $tpl = new TemplateString('{testSecurityClass::testStatic("foo")}');
            $tpl->forceCompilation();

            $this->assertEquals('fooOK', $this->dwoo->get($tpl, array(), $this->compiler));

            $this->policy->disallowMethod('testSecurityClass', 'testStatic');
        }

        /**
         * @expectedException \Dwoo\Security\Exception
         */
        public function testNotAllowedStaticMethod()
        {
            $tpl = new TemplateString('{testSecurityClass::testStatic("foo")}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        /**
         * @expectedException \Dwoo\Security\Exception
         */
        public function testNotAllowedSubExecution()
        {
            $tpl = new TemplateString('{$obj->test(preg_replace_callback("{.}", "mail", "f"))}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array('obj' => new testSecurityClass()), $this->compiler);
        }

        public function testAllowDirectoryGetSet()
        {
            $old = $this->policy->getAllowedDirectories();
            $this->policy->allowDirectory(array('./resources'));
            $this->policy->allowDirectory('./temp');
            $this->assertEquals(array_merge($old, array(
                realpath('./resources') => true,
                realpath('./temp')      => true
            )), $this->policy->getAllowedDirectories());

            $this->policy->disallowDirectory(array('./resources'));
            $this->policy->disallowDirectory('./temp');
            $this->assertEquals($old, $this->policy->getAllowedDirectories());
        }

        public function testAllowPhpGetSet()
        {
            $old = $this->policy->getAllowedPhpFunctions();
            $this->policy->allowPhpFunction(array('a', 'b'));
            $this->policy->allowPhpFunction('c');
            $this->assertEquals(array_merge($old, array(
                'a' => true,
                'b' => true,
                'c' => true
            )), $this->policy->getAllowedPhpFunctions());

            $this->policy->disallowPhpFunction(array('a', 'b'));
            $this->policy->disallowPhpFunction('c');
            $this->assertEquals($old, $this->policy->getAllowedPhpFunctions());
        }
    }
}

namespace
{

    function testphpfunc($input)
    {
        return $input . 'OK';
    }

    class testSecurityClass
    {
        public static function testStatic($input)
        {
            return $input . 'OK';
        }

        public function testOK($input)
        {
            return $input . 'OK';
        }

        public function test($input)
        {
            throw new Exception('can not call');
        }
    }
}