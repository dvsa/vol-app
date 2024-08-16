<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Mapping;

use Dvsa\Olcs\Api\Service\Ebsr\Mapping\TransExchangePublisherXmlFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TransExchangePublisherXmlFactoryTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Mapping
 */
class TransExchangePublisherXmlFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockSl = m::mock(ContainerInterface::class);

        $sut = new TransExchangePublisherXmlFactory();

        $service = $sut->__invoke($mockSl, null);

        $this->assertInstanceOf(SpecificationInterface::class, $service);
    }
}
