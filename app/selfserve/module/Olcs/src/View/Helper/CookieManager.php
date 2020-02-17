<?php

namespace Olcs\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\HelperInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class CookieManagerHelper
 *
 * @package Olcs\View\Helper
 */
class CookieManager extends AbstractHelper implements HelperInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function __invoke()
    {
        return $this->getConfig('cookie-manager');
    }

    private function getConfig(string $name)
    {
        $config =  $this->getServiceLocator()->getServiceLocator()->get('Config');
        return json_encode($config[$name]);
    }
}
