<?php

namespace Olcs\Service;

use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class NavigationFactory
 * @package Olcs\Service
 */
class NavigationFactory extends ConstructedNavigationFactory implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param null $nav
     */
    public function __construct($nav = null)
    {
        $this->config = $nav;
    }

    /**
     * @param $nav
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation($nav)
    {
        $this->config = $nav;
        return $this->createService($this->getServiceLocator());
    }
}
