<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\SetCookie;

class SetCookieGenerator
{
    const COOKIE_PATH = '/';

    /** @var SetCookieFactory */
    private $setCookieFactory;

    /** @var CookieExpiryGenerator */
    private $cookieExpiryGenerator;

    /**
     * Create service instance
     *
     * @param SetCookieFactory $setCookieFactory
     * @param CookieExpiryGenerator $cookieExpiryGenerator
     *
     * @return SetCookieGenerator
     */
    public function __construct(
        SetCookieFactory $setCookieFactory,
        CookieExpiryGenerator $cookieExpiryGenerator
    ) {
        $this->setCookieFactory = $setCookieFactory;
        $this->cookieExpiryGenerator = $cookieExpiryGenerator;
    }

    /**
     * Return a SetCookie instance corresponding to the specified preferences
     *
     * @param Preferences $preferences
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
            $this->cookieExpiryGenerator->generate(),
            self::COOKIE_PATH
        );
    }
}
