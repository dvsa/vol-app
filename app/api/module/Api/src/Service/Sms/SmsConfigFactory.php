<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Transport\Dsn;

final class SmsConfigFactory implements FactoryInterface
{
    private const SCHEME_LIVE = 'govuknotify';
    private const SCHEME_MAILPIT = 'govuknotify+mailpit';

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SmsConfig
    {
        $config = $container->get('config');
        $sms = $config['email']['sms'] ?? [];

        $apiKey = null;
        $mailpit = false;
        $host = null;
        $port = 1025;

        $dsnString = $config['mail']['dsn'] ?? null;
        if (is_string($dsnString) && trim($dsnString) !== '' && !str_contains($dsnString, '%')) {
            try {
                $dsn = Dsn::fromString(trim($dsnString));
            } catch (\Throwable) {
                $dsn = null;
            }

            if ($dsn !== null && $dsn->getScheme() === self::SCHEME_MAILPIT) {
                $mailpit = true;
                $host = $dsn->getHost();
                $port = $dsn->getPort(1025);
            } elseif ($dsn !== null && $dsn->getScheme() === self::SCHEME_LIVE) {
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
            self::setOrNull($sms['send_all_sms_to'] ?? null),
            (bool) ($sms['is_production'] ?? false),
        );
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
