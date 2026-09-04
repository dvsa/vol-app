<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\AwsSdk\Factories;

use Aws\EventBridge\EventBridgeClient;
use Dvsa\Olcs\AwsSdk\Factories\EventBridgeClientFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * The emit these options protect happens inside the application submission transaction, so an
 * unbounded call there holds row locks until something upstream gives up. These assertions are
 * about the bounds, not just that a client comes back.
 */
final class EventBridgeClientFactoryTest extends TestCase
{
    private EventBridgeClientFactory $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new EventBridgeClientFactory();
    }

    public function testItBoundsConnectAndRequestTime(): void
    {
        $http = $this->sut->clientArgs($this->config())['http'];

        $this->assertSame(EventBridgeClientFactory::CONNECT_TIMEOUT_SECONDS, $http['connect_timeout']);
        $this->assertSame(EventBridgeClientFactory::TIMEOUT_SECONDS, $http['timeout']);
    }

    public function testItBoundsRetriesSoTheWorstCaseIsNotMultiplied(): void
    {
        $this->assertSame(EventBridgeClientFactory::RETRIES, $this->sut->clientArgs($this->config())['retries']);
    }

    public function testItRoutesThroughTheConfiguredProxy(): void
    {
        $args = $this->sut->clientArgs($this->config(['proxy' => 'http://proxy.example:3128']));

        $this->assertSame('http://proxy.example:3128', $args['http']['proxy']);
    }

    /**
     * Local development and CI have no egress proxy. Passing an empty one would break them, so the
     * key has to be absent rather than empty.
     */
    public function testItOmitsTheProxyWhenNoneIsConfigured(): void
    {
        $this->assertArrayNotHasKey('proxy', $this->sut->clientArgs($this->config())['http']);
    }

    public function testItOmitsTheProxyWhenConfiguredEmpty(): void
    {
        $this->assertArrayNotHasKey('proxy', $this->sut->clientArgs($this->config(['proxy' => '']))['http']);
    }

    public function testItPassesRegionAndVersionThrough(): void
    {
        $args = $this->sut->clientArgs($this->config());

        $this->assertSame('eu-west-1', $args['region']);
        $this->assertSame('latest', $args['version']);
    }

    public function testItReturnsAnEventBridgeClient(): void
    {
        $sm = m::mock(ServiceManager::class);
        $sm->shouldReceive('get')->with('config')->andReturn($this->config());

        $this->assertInstanceOf(
            EventBridgeClient::class,
            $this->sut->__invoke($sm, EventBridgeClient::class)
        );
    }

    private function config(array $extraAwsOptions = []): array
    {
        return [
            'awsOptions' => [
                'region' => 'eu-west-1',
                'version' => 'latest',
            ] + $extraAwsOptions,
        ];
    }
}
