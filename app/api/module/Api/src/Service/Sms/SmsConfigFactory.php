<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Dvsa\Olcs\Email\Transport\MailDsn;
use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Transport\Dsn;

final class SmsConfigFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SmsConfig
    {
        $config = $container->get('config');
        $sms = $config['email']['sms'] ?? [];

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
                $key = $dsn->getPassword() ?? $dsn->getUser() ?? '';
                $apiKey = $key !== '' ? $key : null;
            }
        }

        return new SmsConfig(
            $apiKey,
            $mailpit,
            $host,
            $port,
            self::setOrNull($sms['template_id'] ?? null),
            self::normalisedNumber(self::setOrNull($sms['send_all_sms_to'] ?? null)),
            (bool) ($sms['is_production'] ?? false),
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

    /** A redirect that is not a valid UK mobile or landline number counts as unset, so a typo cannot enable SMS. */
    private static function normalisedNumber(?string $value): ?string
    {
        return $value === null ? null : SmsPhoneNumber::normalise($value);
    }

    /** Blank, whitespace and the "null" sentinel (any case) mean unset. */
    private static function setOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || strcasecmp($value, 'null') === 0 || preg_match('/^%[^%]+%$/', $value)) {
            return null;
        }

        return $value;
    }
}
