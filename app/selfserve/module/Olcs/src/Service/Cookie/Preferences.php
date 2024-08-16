<?php

namespace Olcs\Service\Cookie;

use RuntimeException;

class Preferences
{
    public const COOKIE_NAME = 'cookie_policy';

    public const KEY_ANALYTICS = 'analytics';
    public const KEY_SETTINGS = 'settings';

    public const KEYS = [
        self::KEY_ANALYTICS,
        self::KEY_SETTINGS,
    ];

    public const DEFAULT_PREFERENCE_VALUE = false;

    /** @var array */
    private $preferences;

    /**
     * Create instance - provide empty array to obtain default settings
     *
     *
     * @return Preferences
     * @throws RuntimeException
     */
    public function __construct(array $preferencesArray)
    {
        if (empty($preferencesArray)) {
            $this->preferences = array_fill_keys(self::KEYS, self::DEFAULT_PREFERENCE_VALUE);
        } else {
            foreach (self::KEYS as $key) {
                if (!array_key_exists($key, $preferencesArray)) {
                    throw new RuntimeException('Preference ' . $key . ' is not present');
                }

                if (!is_bool($preferencesArray[$key])) {
                    throw new RuntimeException('Preference ' . $key . ' is non-bool value');
                }

                $this->preferences[$key] = $preferencesArray[$key];
            }
        }
    }

    /**
     * Get filtered preferences as a key/value array
     *
     * @return array
     */
    public function asArray()
    {
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
        return $this->preferences[$key];
    }
}
