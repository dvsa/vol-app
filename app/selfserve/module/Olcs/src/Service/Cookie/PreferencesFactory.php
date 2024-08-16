<?php

namespace Olcs\Service\Cookie;

class PreferencesFactory
{
    /**
     * Create and return a Preferences instance
     *
     *
     * @return Preferences
     */
    public function create(array $preferencesArray = [])
    {
        return new Preferences($preferencesArray);
    }
}
