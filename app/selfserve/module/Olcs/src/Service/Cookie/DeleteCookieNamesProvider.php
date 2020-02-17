<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\SetCookie;
use Zend\Http\Header\Cookie;

class DeleteCookieNamesProvider
{
    private $cookieNamesProviders = [];

    /**
     * Return a list of cookie names to be deleted in accordance with the provided preferences
     *
     * @param Preferences $preferences
     * @param Cookie $cookie
     *
     * @return array
     */
    public function getNames(Preferences $preferences, Cookie $cookie)
    {
        $cookieNames = [];

        foreach ($this->cookieNamesProviders as $preferenceName => $cookieNamesProvider) {
            if (!$preferences->isActive($preferenceName)) {
                $cookieNames = array_merge(
                    $cookieNames,
                    $cookieNamesProvider->getNames($cookie)
                );
            }
        }

        return $cookieNames;
    }

    /**
     * Register a class that provides a list of cookie names against the specified preference
     *
     * @param string $preferenceName
     * @param CookieNamesProviderInterface $cookieNamesProvider
     */
    public function registerCookieNamesProvider($preferenceName, CookieNamesProviderInterface $cookieNamesProvider)
    {
        $this->cookieNamesProviders[$preferenceName] = $cookieNamesProvider;
    }
}
