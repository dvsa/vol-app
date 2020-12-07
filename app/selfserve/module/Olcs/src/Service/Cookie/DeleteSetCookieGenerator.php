<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\SetCookie;

class DeleteSetCookieGenerator
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
     * Return a SetCookie instance to delete the named cookie
     *
     * @param array $data
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
