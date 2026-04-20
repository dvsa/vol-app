<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Transport\DevNotifyTransport;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransport;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use League\CommonMark\ConverterInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Mailer\Transport\Dsn;

class GovUkNotifyTransportFactoryTest extends MockeryTestCase
{
    public function testSupportsExpectedSchemes(): void
    {
        $factory = $this->buildFactory();

        $this->assertTrue($factory->supports(new Dsn('govuknotify', 'default')));
        $this->assertTrue($factory->supports(new Dsn('govuknotify+mailpit', 'mailpit', null, null, 1025)));
        $this->assertFalse($factory->supports(new Dsn('smtp', 'localhost')));
    }

    public function testCreatesProductionTransport(): void
    {
        $client = m::mock(NotifyClient::class);
        $factory = $this->buildFactory(fn () => $client);

        $transport = $factory->create(new Dsn('govuknotify', 'default', 'user', 'secret-key'));

        $this->assertInstanceOf(GovUkNotifyTransport::class, $transport);
    }

    public function testMissingApiKeyFails(): void
    {
        $factory = $this->buildFactory();
        $this->expectException(\InvalidArgumentException::class);
        $factory->create(new Dsn('govuknotify', 'default'));
    }

    public function testCreatesDevTransport(): void
    {
        $factory = $this->buildFactory();
        $transport = $factory->create(new Dsn('govuknotify+mailpit', 'mailpit', null, null, 1025));
        $this->assertInstanceOf(DevNotifyTransport::class, $transport);
    }

    private function buildFactory(?\Closure $clientFactory = null): GovUkNotifyTransportFactory
    {
        return new GovUkNotifyTransportFactory(
            ['en_GB' => 'tpl-en', 'cy_GB' => 'tpl-cy'],
            $clientFactory ?? static fn () => m::mock(NotifyClient::class),
            m::mock(ConverterInterface::class),
            '<html>{{subject}}{{body}}</html>',
        );
    }
}
