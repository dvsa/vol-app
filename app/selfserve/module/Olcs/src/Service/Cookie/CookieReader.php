<?php

namespace Olcs\Service\Cookie;

use RuntimeException;
use Zend\Http\Header\Cookie;

class CookieReader
{
    /** @var CookieStateFactory */
    private $cookieStateFactory;

    /** @var PreferencesFactory */
    private $preferencesFactory;

    /**
     * Create service instance
     *
     * @param CookieStateFactory $cookieStateFactory
     * @param PreferencesFactory $preferencesFactory
     *
     * @return CookieReader
     */
    public function __construct(CookieStateFactory $cookieStateFactory, PreferencesFactory $preferencesFactory)
    {
        $this->cookieStateFactory = $cookieStateFactory;
        $this->preferencesFactory = $preferencesFactory;
    }

    /**
     * Get CookieState object representing the current preferences cookie state
     *
     * @param mixed $cookie
     *
     * @return CookieState
     */
    public function getState($cookie)
    {
        if (!($cookie instanceof Cookie && isset($cookie[Preferences::COOKIE_NAME]))) {
            return $this->cookieStateFactory->create(false);
        }

        $json = $cookie[Preferences::COOKIE_NAME];

        $contents = json_decode($json, true);
        if (is_null($contents)) {
            return $this->cookieStateFactory->create(false);
        }

        try {
            return $this->cookieStateFactory->create(
                true,
                $this->preferencesFactory->create($contents)
            );
        } catch (RuntimeException $e) {
            return $this->cookieStateFactory->create(false);
        }
    }
}
