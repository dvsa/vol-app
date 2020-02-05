<?php

namespace Olcs\Service\Cookie;

use Zend\Http\Header\SetCookie;

class AcceptAllSetCookieGenerator
{
    /** @var SetCookieGenerator */
    private $setCookieGenerator;

    /** @var PreferencesFactory */
    private $preferencesFactory;

    /**
     * Create service instance
     *
     * @param SetCookieGenerator $setCookieGenerator
     * @param PreferencesFactory $preferencesFactory
     *
     * @return AcceptAllSetCookieGenerator
     */
    public function __construct(SetCookieGenerator $setCookieGenerator, PreferencesFactory $preferencesFactory)
    {
        $this->setCookieGenerator = $setCookieGenerator;
        $this->preferencesFactory = $preferencesFactory;
    }

    /**
     * Return a SetCookie instance representing acceptance of all cookies
     *
     * @return SetCookie
     */
    public function generate()
    {
        return $this->setCookieGenerator->generate(
            $this->preferencesFactory->create()
        );
    }
}
