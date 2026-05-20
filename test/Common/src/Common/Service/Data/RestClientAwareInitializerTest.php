<?php

namespace CommonTest\Common\Service\Data;

use Common\Service\Api\Resolver;
use Common\Service\Data\RestClientAwareInitializer;
use Common\Service\Data\Interfaces\RestClientAware;
use Common\Util\RestClient;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator as Translator;
use stdClass;

class RestClientAwareInitializerTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new RestClientAwareInitializer();
    }

    public function testInvokeWhenInstanceNotRestClientAware(): void
    {
        $instance = m::mock(stdClass::class);
        $serviceLocator = m::mock(ContainerInterface::class);

        $this->assertSame(
            $instance,
            ($this->sut)($serviceLocator, $instance)
        );
    }

    public function testInvokeWhenInstanceRestClientAware(): void
    {
        $lang = 'en_GB';
        $serviceName = 'ServiceName';

        $restClient = m::mock(RestClient::class);
        $restClient->shouldReceive('setLanguage')
            ->with($lang)
            ->once();

        $resolver = m::mock(Resolver::class);
        $resolver->shouldReceive('getClient')
            ->with($serviceName)
            ->andReturn($restClient);

        $translator = m::mock(Translator::class);
        $translator->shouldReceive('getLocale')
            ->withNoArgs()
            ->andReturn($lang);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')
            ->with('ServiceApiResolver')
            ->andReturn($resolver);
        $container->expects('get')
            ->with('translator')
            ->andReturn($translator);

        $instance = m::mock(RestClientAware::class);
        $instance->shouldReceive('setRestClient')
            ->with($restClient)
            ->once();
        $instance->shouldReceive('getServiceName')
            ->withNoArgs()
            ->andReturn($serviceName);

        $this->assertSame(
            $instance,
            ($this->sut)($container, $instance)
        );
    }
}
