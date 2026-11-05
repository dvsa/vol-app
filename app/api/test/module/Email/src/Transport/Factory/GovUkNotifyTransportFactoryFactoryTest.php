<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport\Factory;

use Dvsa\Olcs\Email\Transport\Factory\GovUkNotifyTransportFactoryFactory;
use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class GovUkNotifyTransportFactoryFactoryTest extends TestCase
{
    public function testResolveGuzzleOptionsUsesConfiguredProxy(): void
    {
        $this->assertSame(
            ['proxy' => 'http://proxy.dev.olcs.dev-dvsacloud.uk:3128'],
            GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions('http://proxy.dev.olcs.dev-dvsacloud.uk:3128'),
        );
    }

    public function testResolveGuzzleOptionsIgnoresUnresolvedPlaceholder(): void
    {
        $this->assertSame([], GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions('http://%shd_proxy%'));
    }

    public function testResolveGuzzleOptionsIgnoresEmptyString(): void
    {
        $this->assertSame([], GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions(''));
    }

    public function testResolveGuzzleOptionsIgnoresNull(): void
    {
        $this->assertSame([], GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions(null));
    }

    public function testInvokeUsesNotifyClientFactoryFromContainer(): void
    {
        $key = 'test-11111111-1111-4111-8111-111111111111-22222222-2222-4222-8222-222222222222';
        $client = $this->createStub(NotifyClient::class);
        $clientFactory = $this->createMock(NotifyClientFactory::class);
        $clientFactory->expects($this->once())->method('fromApiKey')->with($key)->willReturn($client);

        $sm = new ServiceManager();
        $sm->setService('config', ['email' => ['notify' => []]]);
        $sm->setService(NotifyClientFactory::class, $clientFactory);

        $factory = (new GovUkNotifyTransportFactoryFactory())($sm, GovUkNotifyTransportFactory::class);
        $this->assertInstanceOf(GovUkNotifyTransportFactory::class, $factory);

        $closure = (new \ReflectionProperty($factory, 'clientFactory'))->getValue($factory);
        $this->assertSame($client, $closure($key));
    }
}
