<?php

namespace Olcs\Service\Cookie;

class CookieExpiryGenerator
{
    /**
     * Get a timestamp representing the expiry time of the cookie
     *
     * @param string $expiry
     *
     * @return int
     */
    public function generate($expiry)
    {
        return strtotime($expiry);
    }
}
