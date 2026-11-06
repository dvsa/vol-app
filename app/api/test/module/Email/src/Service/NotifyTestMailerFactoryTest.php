<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Service\NotifyTestMailerFactory;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransport;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Dvsa\Olcs\Email\Transport\NotifyMessageRecorderInterface;
use League\CommonMark\ConverterInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

#[\PHPUnit\Framework\Attributes\CoversClass(NotifyTestMailerFactory::class)]
final class NotifyTestMailerFactoryTest extends MockeryTestCase
{
    public function testAdminTestSendsWriteNoNotifyMessageRows(): void
    {
        $client = m::mock(NotifyClient::class);
        $client->shouldReceive('sendEmail')->once()->andReturn(['id' => 'notify-1']);

        $recorder = m::mock(NotifyMessageRecorderInterface::class);
        $recorder->shouldNotReceive('recordEmail');

        $govUkFactory = new GovUkNotifyTransportFactory(
            ['en_GB' => 'tpl-en'],
            static fn () => $client,
            m::mock(ConverterInterface::class),
            '<html>{{subject}}{{body}}</html>',
            $recorder,
        );

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')
            ->andReturn(['email' => ['notify_test' => ['dsn' => 'govuknotify://key:test-key@default']]]);
        $container->shouldReceive('get')->with(GovUkNotifyTransportFactory::class)->andReturn($govUkFactory);

        $mailer = (new NotifyTestMailerFactory())($container, 'NotifyTestMailer');

        $email = (new SymfonyEmail())
            ->from(new Address('from@example.com'))
            ->to(new Address('admin@example.com'))
            ->subject('Test')
            ->text('body');
        $email->getHeaders()->addTextHeader(GovUkNotifyTransport::PAYLOAD_HEADER, json_encode(['markdownBody' => 'Hi']));

        $mailer->send($email);
    }
}
