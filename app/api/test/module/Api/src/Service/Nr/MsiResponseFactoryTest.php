<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr;

use Dvsa\Olcs\Api\Service\Nr\MsiResponse;
use Dvsa\Olcs\Api\Service\Nr\MsiResponseFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\XmlTools\Xml\XmlNodeBuilder;
use Psr\Container\ContainerInterface;

class MsiResponseFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $config = [
            'nr' => [
                'compliance_episode' => [
                    'xmlNs' => 'xml ns',
                    'erruVersion' => "3.4",
                ],
            ],
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->once()->andReturn($config);

        $sut = new MsiResponseFactory();
        $service = $sut->__invoke($mockSl, MsiResponse::class);

        $this->assertInstanceOf(MsiResponse::class, $service);
        $this->assertInstanceOf(XmlNodeBuilder::class, $service->getXmlBuilder());
    }

    public function testInvokeMissingConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No config specified for xml ns');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->once()->andReturn([]);
        $sut = new MsiResponseFactory();
        $sut->__invoke($mockSl, MsiResponse::class);
    }
}
