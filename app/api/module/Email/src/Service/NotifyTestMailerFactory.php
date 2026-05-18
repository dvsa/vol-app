<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Builds a {@see NotifyTestMailer} from `config['email']['notify_test']['dsn']`.
 *
 * If the DSN is empty/unset/an unresolved Parameter Store placeholder, the returned mailer
 * is disabled (and the admin "Send test" button hides). This avoids requiring the dependency
 * to be present in cut-over envs.
 */
final class NotifyTestMailerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NotifyTestMailer
    {
        $config = $container->get('config');
        $dsn = $config['email']['notify_test']['dsn'] ?? null;

        /** @var GovUkNotifyTransportFactory $govUkFactory */
        $govUkFactory = $container->get(GovUkNotifyTransportFactory::class);

        return NotifyTestMailer::fromDsn(is_string($dsn) ? $dsn : null, $govUkFactory);
    }
}
