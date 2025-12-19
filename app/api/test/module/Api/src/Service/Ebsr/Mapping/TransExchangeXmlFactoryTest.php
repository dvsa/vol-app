<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Mapping;

use Dvsa\Olcs\Api\Service\Ebsr\Mapping\TransExchangeXmlFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TransExchangeXmlFactoryTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Mapping
 */
class TransExchangeXmlFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockSl = m::mock(ContainerInterface::class);

        $sut = new TransExchangeXmlFactory();

        $service = $sut->__invoke($mockSl, null);

        $this->assertInstanceOf(SpecificationInterface::class, $service);
    }

    /**
     * verify that when parsing xml, routeDescription returns string not array - vol-6809
     */
    public function testRouteDescriptionReturnsStringWhenParsingXml()
    {
        $mockSl = m::mock(ContainerInterface::class);
        $sut = new TransExchangeXmlFactory();
        $specification = $sut->__invoke($mockSl, null);

        // Create a minimal TransXChange XML with a Route Description
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<TransXChange xmlns="http://www.transxchange.org.uk/" SchemaVersion="2.4">
    <Routes>
        <Route>
            <Description>Darlington - Stockton - Middlesbrough</Description>
        </Route>
    </Routes>
</TransXChange>
XML;

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $result = $specification->apply($dom->documentElement);

        $this->assertArrayHasKey('routeDescription', $result);
        $this->assertIsString(
            $result['routeDescription'],
            'routeDescription MUST be a string.'
        );
        $this->assertFalse(
            is_array($result['routeDescription']),
            'routeDescription must NOT be an array'
        );

        $this->assertEquals('Darlington - Stockton - Middlesbrough', $result['routeDescription']);
    }
}
