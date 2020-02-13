<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\Cookie;

class SettingsCookieNamesProvider implements CookieNamesProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNames(Cookie $cookie)
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
