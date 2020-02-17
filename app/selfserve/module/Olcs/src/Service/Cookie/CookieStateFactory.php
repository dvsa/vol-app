<?php

namespace Olcs\Service\Cookie;

class CookieStateFactory
{
    /**
     * Create and return a CookieState instance
     *
     * @param bool $isValid
     * @param Preferences|null $preferences
     *
     * @return CookieState
     */
    public function create($isValid, ?Preferences $preferences = null)
    {
        return new CookieState($isValid, $preferences);
    }
}
