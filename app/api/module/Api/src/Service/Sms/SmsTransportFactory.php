<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

final class SmsTransportFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SmsTransportInterface
    {
        /** @var SmsConfig $config */
        $config = $container->get(SmsConfig::class);

        if ($config->mailpit) {
            $email = $container->get('config')['email'] ?? [];
            $transport = new EsmtpTransport($config->mailpitHost ?? 'localhost', $config->mailpitPort, false);

            return new MailpitSmsTransport(
                new Mailer($transport),
                $email['from_email'] ?? 'sms@localhost',
                $email['from_name'] ?? '',
            );
        }

        return new NotifySmsTransport(
            $container->get(NotifyClientFactory::class)->fromApiKey((string) $config->apiKey)
        );
    }
}
