<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\SetCookie;

class AcceptAllSetCookieGenerator
{
    /** @var PreferencesSetCookieGenerator */
    private $preferencesSetCookieGenerator;

    /** @var PreferencesFactory */
    private $preferencesFactory;

    /**
     * Create service instance
     *
     * @param PreferencesSetCookieGenerator $preferencesSetCookieGenerator
     * @param PreferencesFactory $preferencesFactory
     *
     * @return AcceptAllSetCookieGenerator
     */
    public function __construct(
        PreferencesSetCookieGenerator $preferencesSetCookieGenerator,
        PreferencesFactory $preferencesFactory
    ) {
        $this->preferencesSetCookieGenerator = $preferencesSetCookieGenerator;
        $this->preferencesFactory = $preferencesFactory;
    }

    /**
     * Return a SetCookie instance representing acceptance of all cookies
     *
     * @return SetCookie
     */
    public function generate()
    {
        return $this->preferencesSetCookieGenerator->generate(
            $this->preferencesFactory->create()
        );
    }
}
