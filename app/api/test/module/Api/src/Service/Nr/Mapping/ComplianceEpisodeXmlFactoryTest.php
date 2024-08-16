<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Mapping;

use Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXml;
use Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXmlFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\XmlTools\Filter\MapXmlFile;
use Psr\Container\ContainerInterface;

class ComplianceEpisodeXmlFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $config = [
            'nr' => [
                'compliance_episode' => [
                    'xmlNs' => 'xml ns info'
                ]
            ]
        ];

        $mockMapXmlFile = m::mock(MapXmlFile::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->once()->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->once()->with(MapXmlFile::class)->andReturn($mockMapXmlFile);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);

        $sut = new ComplianceEpisodeXmlFactory();

        $service = $sut->__invoke($mockSl, ComplianceEpisodeXml::class);

        $this->assertInstanceOf(ComplianceEpisodeXml::class, $service);
    }

    public function testInvokeMissingConfig()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing INR service config');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);

        $sut = new ComplianceEpisodeXmlFactory();
        $sut->__invoke($mockSl, ComplianceEpisodeXml::class);
    }
}
