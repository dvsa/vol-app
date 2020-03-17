<?php

namespace Olcs\Service\Cookie;

class PreferencesFactory
{
    /**
     * Create and return a Preferences instance
     *
     * @param array $preferencesArray
     *
     * @return Preferences
     */
    public function create(array $preferencesArray = [])
    {
        return new Preferences($preferencesArray);
    }
}
