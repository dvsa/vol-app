<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\SetCookie;

class PreferencesSetCookieGenerator
{
    public const COOKIE_PATH = '/';

    /**
     * Create service instance
     *
     *
     * @return PreferencesSetCookieGenerator
     */
    public function __construct(private readonly SetCookieFactory $setCookieFactory, private readonly CookieExpiryGenerator $cookieExpiryGenerator)
    {
    }

    /**
     * Return a SetCookie instance corresponding to the specified preferences
     *
     *
     * @return SetCookie
     */
    public function generate(Preferences $preferences)
    {
        return $this->setCookieFactory->create(
            Preferences::COOKIE_NAME,
            json_encode(
                $preferences->asArray()
            ),
            $this->cookieExpiryGenerator->generate('+1 year'),
            self::COOKIE_PATH
        );
    }
}
