<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Dvsa\Olcs\Email\Service\NotifyClientFactoryFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class NotifyClientFactoryTest extends TestCase
{
    public function testFromApiKeyReturnsNotifyClient(): void
    {
        $key = 'test-11111111-1111-4111-8111-111111111111-22222222-2222-4222-8222-222222222222';
        $this->assertInstanceOf(NotifyClient::class, (new NotifyClientFactory())->fromApiKey($key));
    }

    public function testResolveGuzzleOptions(): void
    {
        $this->assertSame(['proxy' => 'http://p:3128'], NotifyClientFactory::resolveGuzzleOptions('http://p:3128'));
        $this->assertSame([], NotifyClientFactory::resolveGuzzleOptions('http://%shd_proxy%'));
        $this->assertSame([], NotifyClientFactory::resolveGuzzleOptions(''));
        $this->assertSame([], NotifyClientFactory::resolveGuzzleOptions(null));
    }

    public function testServiceFactoryBuildsFromConfig(): void
    {
        $sm = new ServiceManager();
        $sm->setService('config', ['email' => ['notify' => ['proxy' => 'http://p:3128']]]);

        $this->assertInstanceOf(
            NotifyClientFactory::class,
            (new NotifyClientFactoryFactory())($sm, NotifyClientFactory::class)
        );
    }
}
