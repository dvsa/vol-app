<?php

namespace Olcs\Service\Cookie;

class CurrentPreferencesProvider
{
    /**
     * Create service instance
     *
     *
     * @return CurrentPreferencesProvider
     */
    public function __construct(private readonly CookieReader $cookieReader, private readonly PreferencesFactory $preferencesFactory)
    {
    }

    /**
     * Get a key/value array of the current preferences, or default preferences if none are available
     *
     *
     * @return Preferences|null
     */
    public function getPreferences(mixed $cookie): ?Preferences
    {
        $cookieState = $this->cookieReader->getState($cookie);

        if ($cookieState->isValid()) {
            return $cookieState->getPreferences();
        }

        return $this->preferencesFactory->create();
    }
}
