<?php

namespace Olcs\Service\Cookie;

class CurrentPreferencesProvider
{
    /** @var CookieReader */
    private $cookieReader;

    /** @var PreferencesFactory */
    private $preferencesFactory;

    /**
     * Create service instance
     *
     * @param CookieReader $cookieReader
     * @param PreferencesFactory $preferencesFactory
     *
     * @return CurrentPreferencesProvider
     */
    public function __construct(
        CookieReader $cookieReader,
        PreferencesFactory $preferencesFactory
    ) {
        $this->cookieReader = $cookieReader;
        $this->preferencesFactory = $preferencesFactory;
    }

    /**
     * Get a key/value array of the current preferences, or default preferences if none are available
     *
     * @param mixed $cookie
     *
     * @return Preferences
     */
    public function getPreferences($cookie)
    {
        $cookieState = $this->cookieReader->getState($cookie);

        if ($cookieState->isValid()) {
            return $cookieState->getPreferences();
        }
        
        return $this->preferencesFactory->create();
    }
}
