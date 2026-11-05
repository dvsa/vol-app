<?php

namespace Common\Preference;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Header\SetCookie;
use Psr\Container\ContainerInterface;

class Language implements FactoryInterface
{
    public const OPTION_EN = 'en';

    public const OPTION_CY = 'cy';

    private $options = [
        self::OPTION_EN => 'English',
        self::OPTION_CY => 'Cymraeg'
    ];

    /**
     * @var SetCookie
     */
    private $requestCookie;

    private $preference;

    private $key = 'langPref';

    private ?Response $response = null;

    private bool $cookieSent = false;

    /**
     * @psalm-param 'XX'|'en' $preference
     */
    public function setPreference($preference): void
    {
        if (!array_key_exists($preference, $this->options)) {
            throw new \Exception('Invalid language preference option');
        }

        $this->preference = $preference;

        $this->requestCookie->setValue($this->preference);
        $this->sendCookie();
    }

    public function getPreference()
    {
        return $this->preference;
    }

    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Language
    {
        $request = $container->get('Request');
        // if not an Http request (eg Console request) then don't do anything as
        // methods below only exists on Http Requests
        if (!$request instanceof Request) {
            return $this;
        }

        $cookie = $request->getCookie();
        $this->preference = self::OPTION_EN;
        $this->requestCookie = new SetCookie();
        $this->response = $container->get('Response');

        $hasChosen = $cookie instanceof Cookie
            && isset($cookie[$this->key])
            && array_key_exists($cookie[$this->key], $this->options);

        if ($hasChosen) {
            $this->preference = $cookie[$this->key];
        }

        $this->requestCookie->setName($this->key);
        $this->requestCookie->setValue($this->preference);
        $this->requestCookie->setPath('/');
        $this->requestCookie->setSameSite('Strict');

        $remembersSettings = !$container->has('CookieCookieReader')
            || $container->get('CookieCookieReader')->getState($cookie)->isActive('settings');

        if ($remembersSettings) {
            // the user has accepted the cookies that remember their settings, so keep the
            // language for their next visit
            $this->requestCookie->setExpires(strtotime('+10 years'));
            $this->sendCookie();
            return $this;
        }

        // Without that consent, a language the user chose is kept only until they close the
        // browser, so they can carry on in it. Rewriting the cookie with no expiry also turns
        // one kept from an earlier consent into a session cookie. No cookie is set for someone
        // who hasn't chosen a language (setPreference sets it when they do).
        if ($hasChosen) {
            $this->sendCookie();
        }

        return $this;
    }

    private function sendCookie(): void
    {
        if ($this->cookieSent || $this->response === null) {
            return;
        }

        $this->response->getHeaders()->addHeader($this->requestCookie);
        $this->cookieSent = true;
    }
}
