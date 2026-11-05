<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\Cookie;

class SettingsCookieNamesProvider implements CookieNamesProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * langPref is not listed: without consent to these cookies, Common\Preference\Language turns it
     * into a session cookie instead, so the user stays in the language they chose.
     *
     * @return (null|string)[][]
     *
     * @psalm-return list{array{name: 'cookie_seen', domain: null}}
     */
    #[\Override]
    public function getNames(Cookie $cookie): array
    {
        return [
            [
                'name' => 'cookie_seen',
                'domain' => null
            ]
        ];
    }
}
