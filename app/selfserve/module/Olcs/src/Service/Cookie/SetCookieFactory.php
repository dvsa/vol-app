<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\SetCookie;

class SetCookieFactory
{
    /**
     * Create and return a SetCookie instance
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     *
     * @return SetCookie
     */
    public function create($name, $value, $expires, $path)
    {
        return new SetCookie($name, $value, $expires, $path);
    }
}
