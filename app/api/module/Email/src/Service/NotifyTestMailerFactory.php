<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Builds a {@see NotifyTestMailer} from `notify.email.test_dsn` (see {@see NotifyConfig}).
 *
 * If the DSN is empty/unset/an unresolved Parameter Store placeholder, the returned mailer
 * is disabled (and the admin "Send test" button hides). This avoids requiring the dependency
 * to be present in cut-over envs.
 */
final class NotifyTestMailerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NotifyTestMailer
    {
        $config = $container->get('config');
        $dsn = NotifyConfig::testDsn($config);

        /** @var GovUkNotifyTransportFactory $govUkFactory */
        $govUkFactory = $container->get(GovUkNotifyTransportFactory::class);

        // test sends are not real correspondence, so they write no notify_message rows, and they
        // use the key in their own DSN, never notify.api_key
        return NotifyTestMailer::fromDsn($dsn, $govUkFactory->forTestSends());
    }
}
