<?php

/**
 * Cookie Banner
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Mvc;

use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;
use Zend\View\Helper\Placeholder;

/**
 * Cookie Banner
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieBanner implements FactoryInterface
{
    const KEY = 'cookie_seen';

    /**
     * @var SetCookie
     */
    private $requestCookie;

    private $seen;

    /**
     * @var Placeholder
     */
    private $placeholder;

    /**
     * @var Response
     */
    private $response;

    private $cookie;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cookie = $serviceLocator->get('Request')->getCookie();
        $this->response = $serviceLocator->get('Response');
        $this->placeholder = $serviceLocator->get('ViewHelperManager')->get('Placeholder');

        return $this;
    }

    /**
     * That is the question
     */
    public function toSeeOrNotToSee()
    {
        if ($this->cookie instanceof Cookie && isset($this->cookie[self::KEY])) {
            $this->seen = $this->cookie[self::KEY];
        }

        if ($this->seen !== null) {
            $this->placeholder->getContainer('showCookieBanner')->set(false);
        } else {
            $this->placeholder->getContainer('showCookieBanner')->set(true);
            $this->markAsSeen();
        }
    }

    protected function markAsSeen()
    {
        $this->requestCookie = new SetCookie();
        $this->requestCookie->setName(self::KEY);
        $this->requestCookie->setValue(1);
        $this->requestCookie->setPath('/');
        $this->requestCookie->setExpires(strtotime('+1 month'));

        /** @var Response $response */
        $this->response->getHeaders()->addHeader($this->requestCookie);
    }
}
