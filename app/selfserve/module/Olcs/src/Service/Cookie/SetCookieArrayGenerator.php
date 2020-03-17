<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;

class SetCookieArrayGenerator
{
    /** @var DeleteCookieNamesProvider */
    private $deleteCookieNamesProvider;

    /** @var PreferencesSetCookieGenerator */
    private $preferencesSetCookieGenerator;

    /** @var DeleteSetCookieGenerator */
    private $deleteSetCookieGenerator;

    /**
     * Create service instance
     *
     * @param DeleteCookieNamesProvider $deleteCookieNamesProvider
     * @param PreferencesSetCookieGenerator $preferencesSetCookieGenerator
     * @param DeleteSetCookieGenerator $deleteSetCookieGenerator
     *
     * @return SetCookieArrayGenerator
     */
    public function __construct(
        DeleteCookieNamesProvider $deleteCookieNamesProvider,
        PreferencesSetCookieGenerator $preferencesSetCookieGenerator,
        DeleteSetCookieGenerator $deleteSetCookieGenerator
    ) {
        $this->deleteCookieNamesProvider = $deleteCookieNamesProvider;
        $this->preferencesSetCookieGenerator = $preferencesSetCookieGenerator;
        $this->deleteSetCookieGenerator = $deleteSetCookieGenerator;
    }

    /**
     * Return an array of SetCookie instances corresponding to the specified preferences
     *
     * @param Preferences $preferences
     * @param Cookie $cookie
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
