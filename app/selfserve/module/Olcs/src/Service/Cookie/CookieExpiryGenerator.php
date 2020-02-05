<?php

namespace Olcs\Service\Cookie;

class CookieExpiryGenerator
{
    const COOKIE_EXPIRY = '+1 year';

    /**
     * Get a timestamp representing the expiry time of the cookie
     *
     * @return int
     */
    public function generate()
    {
        return strtotime(self::COOKIE_EXPIRY);
    }
}
