<?php

namespace Olcs\Service\Cookie;

use RuntimeException;
use Laminas\Http\Header\Cookie;

class CookieReader
{
    /**
     * Create service instance
     *
     *
     * @return CookieReader
     */
    public function __construct(private readonly CookieStateFactory $cookieStateFactory, private readonly PreferencesFactory $preferencesFactory)
    {
    }

    /**
     * Get CookieState object representing the current preferences cookie state
     *
     *
     * @return CookieState
     */
    public function getState(mixed $cookie)
    {
        if (!($cookie instanceof Cookie && isset($cookie[Preferences::COOKIE_NAME]))) {
            return $this->cookieStateFactory->create(false);
        }

        $json = $cookie[Preferences::COOKIE_NAME];

        $contents = json_decode((string) $json, true);
        if (is_null($contents)) {
            return $this->cookieStateFactory->create(false);
        }

        try {
            return $this->cookieStateFactory->create(
                true,
                $this->preferencesFactory->create($contents)
            );
        } catch (RuntimeException) {
            return $this->cookieStateFactory->create(false);
        }
    }
}
