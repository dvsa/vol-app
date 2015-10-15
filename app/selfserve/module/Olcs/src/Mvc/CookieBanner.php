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
    /**
     * @var SetCookie
     */
    private $requestCookie;

    private $key = 'cookie_seen';

    private $seen;

    /**
     * @var Placeholder
     */
    private $placeholder;

    /**
     * @var Response
     */
    private $response;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cookie = $serviceLocator->get('Request')->getCookie();

        $this->placeholder = $serviceLocator->get('ViewHelperManager')->get('Placeholder');
        $this->response = $serviceLocator->get('Response');

        if ($cookie instanceof Cookie && isset($cookie[$this->key])) {
            $this->seen = $cookie[$this->key];
        }

        return $this;
    }

    public function toSeeOrNotToSee()
    {
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
        $this->requestCookie->setName($this->key);
        $this->requestCookie->setValue(1);
        $this->requestCookie->setPath('/');
        $this->requestCookie->setExpires(strtotime('+1 month'));

        /** @var Response $response */
        $this->response->getHeaders()->addHeader($this->requestCookie);
    }
}
