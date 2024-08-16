<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\SetCookie;

class SetCookieFactory
{
    /**
     * Create and return a SetCookie instance
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string|null $domain
     *
     * @return SetCookie
     */
    public function create($name, $value, $expires, $path, $domain = null)
    {
        return new SetCookie($name, $value, $expires, $path, $domain);
    }
}
