<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClientFactory;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Validator\Xsd;
use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TransExchangeClientFactoryTest extends TestCase
{
    public function testInvokeNoConfig(): void
    {
        $this->expectException(\RuntimeException::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);

        $sut = new TransExchangeClientFactory();
        $sut->__invoke($mockSl, TransExchangeClient::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tesInvokeToggleOn(): void
    {
        $oAuthOptions = ['options'];
        $config = [
            'ebsr' => [
                'transexchange_publisher' => [
                    'new_uri' => 'http://txc.dev/',
                    'options' => ['argseparator' => '~'],
                    'template_file' => 'template.xml',
                    'oauth2' => $oAuthOptions,
                ],
            ],
        ];

        $mockSpec = m::mock(SpecificationInterface::class);
        $mockFilter = m::mock(MapXmlFile::class);
        $mockFilter->shouldReceive('setMapping')
            ->once()
            ->with($mockSpec);

        $mockParser = m::mock(ParseXmlString::class);
        $mockXsdValidator = m::mock(Xsd::class);
        $mockXsdValidator->shouldReceive('setXsd')
            ->once()
            ->with(TransExchangeClientFactory::PUBLISH_XSD);

        $mockTokenProvider = m::mock(Provider::class);
        $mockTokenProvider->expects('getToken')->withNoArgs()->andReturn('token');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('ValidatorManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('TransExchangePublisherXmlMapping')->andReturn($mockSpec);
        $mockSl->shouldReceive('get')->with(MapXmlFile::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(ParseXmlString::class)->andReturn($mockParser);
        $mockSl->shouldReceive('get')->with(Xsd::class)->andReturn($mockXsdValidator);
        $mockSl->shouldReceive('build')->with(Provider::class, $oAuthOptions)->andReturn($mockTokenProvider);

        $sut = new TransExchangeClientFactory();
        $service = $sut->__invoke($mockSl, TransExchangeClient::class);

        $this->assertInstanceOf(TransExchangeClient::class, $service);
    }
}
