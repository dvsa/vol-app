<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Email\Service\NotifyConfig;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Dvsa\Olcs\Email\Transport\MailDsn;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Transport\Dsn;

final class SmsConfigFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SmsConfig
    {
        $config = $container->get('config');

        $apiKey = null;
        $mailpit = false;
        $host = null;
        $port = 1025;

        $dsnString = MailDsn::configured($config['mail']['dsn'] ?? null);
        if ($dsnString !== null) {
            try {
                $dsn = Dsn::fromString($dsnString);
            } catch (\Throwable) {
                $dsn = null;
            }

            if ($dsn !== null && $dsn->getScheme() === GovUkNotifyTransportFactory::SCHEME_DEV) {
                $mailpit = true;
                $host = $dsn->getHost();
                $port = $dsn->getPort(1025);
            } elseif ($dsn !== null && $dsn->getScheme() === GovUkNotifyTransportFactory::SCHEME_PRODUCTION) {
                // the same key email uses: the DSN's own, else notify.api_key
                $apiKey = GovUkNotifyTransportFactory::keyFromDsn($dsn) ?? NotifyConfig::apiKey($config);
            }
        }

        return new SmsConfig(
            $apiKey,
            $mailpit,
            $host,
            $port,
            NotifyConfig::smsTemplateId($config),
            self::environment(),
        );
    }

    /** Lowercased ENVIRONMENT_NAME, as config.global.php reads it, or "local" when unset. */
    private static function environment(): string
    {
        $environment = getenv('ENVIRONMENT_NAME');
        $environment = is_string($environment) ? strtolower(trim($environment)) : '';

        return $environment !== '' ? $environment : 'local';
    }
}
