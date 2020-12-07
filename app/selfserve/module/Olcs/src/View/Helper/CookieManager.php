<?php

namespace Olcs\View\Helper;

use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;

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
