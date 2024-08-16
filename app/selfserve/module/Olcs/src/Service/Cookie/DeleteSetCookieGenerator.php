<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\SetCookie;

class DeleteSetCookieGenerator
{
    public const COOKIE_PATH = '/';

    /**
     * Create service instance
     *
     *
     * @return SetCookieArrayGenerator
     */
    public function __construct(private readonly SetCookieFactory $setCookieFactory, private readonly CookieExpiryGenerator $cookieExpiryGenerator)
    {
    }

    /**
     * Return a SetCookie instance to delete the named cookie
     *
     *
     * @return SetCookie
     */
    public function generate(array $data)
    {
        return $this->setCookieFactory->create(
            $data['name'],
            '',
            $this->cookieExpiryGenerator->generate('-1 year'),
            self::COOKIE_PATH,
            $data['domain']
        );
    }
}
