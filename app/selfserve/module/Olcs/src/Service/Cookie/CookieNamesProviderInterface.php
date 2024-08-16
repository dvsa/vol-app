<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\Cookie;
use Laminas\Http\Header\SetCookie;

interface CookieNamesProviderInterface
{
    /**
     * Return a list of cookie names to be deleted when a preference is disabled
     *
     * @return SetCookie
     */
    public function getNames(Cookie $cookie);
}
