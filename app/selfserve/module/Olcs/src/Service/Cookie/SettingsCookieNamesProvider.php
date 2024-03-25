<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\Cookie;

class SettingsCookieNamesProvider implements CookieNamesProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return (null|string)[][]
     *
     * @psalm-return list{array{name: 'langPref', domain: null}, array{name: 'cookie_seen', domain: null}}
     */
    public function getNames(Cookie $cookie): array
    {
        return [
            [
                'name' => 'langPref',
                'domain' => null
            ],
            [
                'name' => 'cookie_seen',
                'domain' => null
            ]
        ];
    }
}
