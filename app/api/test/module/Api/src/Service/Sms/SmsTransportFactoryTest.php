<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Service\Sms\MailpitSmsTransport;
use Dvsa\Olcs\Api\Service\Sms\NotifySmsTransport;
use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Api\Service\Sms\SmsTransportFactory;
use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(SmsTransportFactory::class)]
final class SmsTransportFactoryTest extends TestCase
{
    private const string UUID = '3d6d4f42-1b0e-4d57-9a53-0c1a0b6a7f10';

    private function container(SmsConfig $config): ServiceManager
    {
        $clientFactory = new NotifyClientFactory();

        return new ServiceManager(['services' => [
            SmsConfig::class => $config,
            NotifyClientFactory::class => $clientFactory,
            'config' => ['email' => ['from_email' => 'a@b.c', 'from_name' => 'N']],
        ]]);
    }

    public function testLiveKeyGivesNotifyTransport(): void
    {
        $c = $this->container(new SmsConfig('key-' . self::UUID . '-' . self::UUID, false, null, 1025, 't', null, true));
        $this->assertInstanceOf(NotifySmsTransport::class, (new SmsTransportFactory())($c, 'x'));
    }

    public function testMailpitGivesMailpitTransport(): void
    {
        $c = $this->container(new SmsConfig(null, true, 'mailpit', 1025, 't', '+447700900999', false));
        $this->assertInstanceOf(MailpitSmsTransport::class, (new SmsTransportFactory())($c, 'x'));
    }
}
