<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\Cookie;

class AnalyticsCookieNamesProvider implements CookieNamesProviderInterface
{
    const GAT_PREFIX = '_gat_';

    const LEGACY_COOKIE_DOMAIN = '.vehicle-operator-licensing.service.gov.uk';

    /** @var string */
    private $domain;

    /**
     * Create service instance
     *
     * @param string $domain
     *
     * @return AnalyticsCookieNamesProvider
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames(Cookie $cookie)
    {
        $names = [
            '_gid',
            '_gat',
            '_ga'
        ];

        $cookieArray = $cookie->getArrayCopy();
        foreach ($cookieArray as $cookieName => $cookieValue) {
            if (substr($cookieName, 0, strlen(self::GAT_PREFIX)) == self::GAT_PREFIX) {
                $names[] = $cookieName;
            }
        }

        $augmentedNames = [];
        foreach ($names as $name) {
            $augmentedNames[] = [
                'name' => $name,
                'domain' => $this->domain
            ];

            if (strpos($this->domain, self::LEGACY_COOKIE_DOMAIN) !== false) {
                $augmentedNames[] = [
                    'name' => $name,
                    'domain' => self::LEGACY_COOKIE_DOMAIN
                ];
            }
        }

        return $augmentedNames;
    }
}
