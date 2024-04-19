<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\Cookie;
use Laminas\Http\Header\SetCookie;

class SetCookieArrayGenerator
{
    /**
     * Create service instance
     *
     *
     * @return SetCookieArrayGenerator
     */
    public function __construct(private DeleteCookieNamesProvider $deleteCookieNamesProvider, private PreferencesSetCookieGenerator $preferencesSetCookieGenerator, private DeleteSetCookieGenerator $deleteSetCookieGenerator)
    {
    }

    /**
     * Return an array of SetCookie instances corresponding to the specified preferences
     *
     *
     * @return array
     */
    public function generate(Preferences $preferences, Cookie $cookie)
    {
        $cookieNames = $this->deleteCookieNamesProvider->getNames($preferences, $cookie);
        foreach ($cookieNames as $cookieName) {
            $setCookies[] = $this->deleteSetCookieGenerator->generate($cookieName);
        }

        $setCookies[] = $this->preferencesSetCookieGenerator->generate($preferences);

        return $setCookies;
    }
}
