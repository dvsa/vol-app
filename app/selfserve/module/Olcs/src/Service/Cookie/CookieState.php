<?php

namespace Olcs\Service\Cookie;

use Exception;
use RuntimeException;

class CookieState
{
    /**
     * Create instance
     *
     * @param bool $isValid
     * @param Preferences|null $preferences
     *
     * @return CookieState
     */
    public function __construct($isValid, ?Preferences $preferences = null)
    {
        $this->isValid = $isValid;
        $this->preferences = $preferences;
    }

    /**
     * Whether the cookie is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Get the Preferences object if the cookie is valid
     *
     * @return Preferences
     *
     * @throw RuntimeException if the cookie is not valid
     */
    public function getPreferences()
    {
        if (!$this->isValid()) {
            throw new RuntimeException('Preferences are unavailable when cookie state is invalid');
        }

        return $this->preferences;
    }

    /**
     * Is cookie preference active for a given key
     *
     * @param string $key Cookie key
     *
     * @return bool
     */
    public function isActive($key)
    {
        try {
            return $this->getPreferences()->isActive($key);
        } catch (Exception $e) {
            // swallow any exception
        }

        // return default preference value
        return Preferences::DEFAULT_PREFERENCE_VALUE;
    }
}
