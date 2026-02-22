<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\Cookie;

class AnalyticsCookieNamesProvider implements CookieNamesProviderInterface
{
    public const GAT_PREFIX = '_gat_';

    public const LEGACY_COOKIE_DOMAIN = '.vehicle-operator-licensing.service.gov.uk';

    /**
     * Create service instance
     *
     * @param string $domain
     *
     * @return AnalyticsCookieNamesProvider
     */
    public function __construct(private $domain)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @return (int|string)[][]
     *
     * @psalm-return non-empty-list<array{domain: string, name: array-key}>
     */
    public function getNames(Cookie $cookie): array
    {
        $names = [
            '_gid',
            '_gat',
            '_ga'
        ];

        $cookieArray = $cookie->getArrayCopy();
        foreach ($cookieArray as $cookieName => $cookieValue) {
            if (str_starts_with((string) $cookieName, self::GAT_PREFIX)) {
                $names[] = $cookieName;
            }
        }

        $augmentedNames = [];
        foreach ($names as $name) {
            $augmentedNames[] = [
                'name' => $name,
                'domain' => $this->domain
            ];

            if (str_contains($this->domain, self::LEGACY_COOKIE_DOMAIN)) {
                $augmentedNames[] = [
                    'name' => $name,
                    'domain' => self::LEGACY_COOKIE_DOMAIN
                ];
            }
        }

        return $augmentedNames;
    }
}
