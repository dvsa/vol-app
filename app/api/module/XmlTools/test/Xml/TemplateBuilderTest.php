<?php

namespace OlcsTest\XmlTools\Xml;

use Olcs\XmlTools\Xml\TemplateBuilder;
use org\bovigo\vfs\vfsStream;

/**
 * Class TemplateBuilderTest
 * @package XmlTools\src\Xml
 */
class TemplateBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testBuildTemplate(): void
    {
        vfsStream::setup('root');
        $uri = vfsStream::url('root/template.xml');
        $template = "<?xml version=\"1.0\"?>\n<document><substitute_me></substitute_me></document>\n";
        file_put_contents($uri, $template);

        $templateBuilder = new TemplateBuilder();

        $result = $templateBuilder->buildTemplate($uri, ['substitute_me' => 'Node Value<&>']);

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<document><substitute_me>Node Value&lt;&amp;&gt;</substitute_me></document>\n",
            $result
        );
        // assert that the builder hasn't changed the original template file.
        $this->assertEquals($template, file_get_contents($uri));
    }
}
