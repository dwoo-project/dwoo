<?php

namespace Dwoo\Tests
{

    use Dwoo\Template\File as TemplateFile;

    /**
     * Class TemplateTest
     *
     * @package Dwoo\Tests
     */
    class TemplateTest extends BaseTests
    {

        public function testIncludePath()
        {
            // no include path
            $tpl = new TemplateFile('test.html');
            $this->assertEquals('test.html', $tpl->getResourceIdentifier());

            // include path in constructor
            $tpl = new TemplateFile('test.html', null, null, null, __DIR__ . DIRECTORY_SEPARATOR . 'resources');
            $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'test.html', $tpl->getResourceIdentifier());

            // set include path as string
            $tpl->setIncludePath(__DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR);
            $this->assertThat($tpl->getResourceIdentifier(), new DwooConstraintPathEquals(__DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test.html'));

            // set include path as array
            $tpl->setIncludePath(array(
                __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'subfolder2',
                __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR
            ));
            $this->assertThat($tpl->getResourceIdentifier(), new DwooConstraintPathEquals(__DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'subfolder2' . DIRECTORY_SEPARATOR . 'test.html'));
        }
    }
}