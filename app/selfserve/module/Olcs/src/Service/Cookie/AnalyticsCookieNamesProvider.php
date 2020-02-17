<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\Cookie;

class AnalyticsCookieNamesProvider implements CookieNamesProviderInterface
{
    const GAT_PREFIX = '_gat_';

    /** @var string */
    private $hostname;

    /**
     * Create service instance
     *
     * @param string $hostname
     *
     * @return AnalyticsCookieNamesProvider
     */
    public function __construct($hostname)
    {
        $this->hostname = $hostname;
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
                'domain' => '.' . $this->hostname
            ];
        }

        return $augmentedNames;
    }
}
