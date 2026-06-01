<?php

namespace OlcsTest\XmlTools\Filter;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\XmlTools\Filter\MapXmlFile;

/**
 * Class MapXmlFileTest
 * @package OlcsTest\XmlTools\Filter
 */
class MapXmlFileTest extends TestCase
{
    public function testFilter(): void
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML('<Doc></Doc>');

        $mapped = ['other' => 'data'];

        $mockMapper = m::mock(\Olcs\XmlTools\Xml\Specification\SpecificationInterface::class);
        $mockMapper->shouldReceive('apply')->andReturn($mapped);

        $mapXmlFile = new MapXmlFile();
        $mapXmlFile->setMapping($mockMapper);

        $this->assertEquals($mapped, $mapXmlFile->filter($domDocument));
    }
}
