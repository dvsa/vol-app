<?php

declare(strict_types=1);

namespace OlcsTest\XmlTools\Validator;

use Psr\Container\ContainerInterface;
use Olcs\XmlTools\Validator\Xsd;
use Olcs\XmlTools\Validator\XsdFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class XsdFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $config = ['xsd_mappings' => ['test' => 'test.path']];

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('Config')->andReturn($config);

        $xsdFactory = new XsdFactory();

        $service = $xsdFactory->__invoke($container, Xsd::class);

        $this->assertInstanceOf(Xsd::class, $service);
        $this->assertEquals($config['xsd_mappings'], $service->getMappings());
    }
}
