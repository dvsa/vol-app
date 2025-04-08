<?php

namespace Olcs\Service\Cookie;

use Laminas\Http\Header\SetCookie;

class AcceptAllSetCookieGenerator
{
    /**
     * Create service instance
     *
     *
     * @return AcceptAllSetCookieGenerator
     */
    public function __construct(private readonly PreferencesSetCookieGenerator $preferencesSetCookieGenerator, private readonly PreferencesFactory $preferencesFactory)
    {
    }

    /**
     * Return a SetCookie instance representing acceptance of all cookies
     *
     * @return SetCookie
     */
    public function generate(?bool $state = true)
    {
        return $this->preferencesSetCookieGenerator->generate(
            $this->preferencesFactory->create(
                array_fill_keys(Preferences::KEYS, $state)
            )
        );
    }
}
