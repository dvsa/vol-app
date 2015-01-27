<?php
namespace Olcs\Navigation;
use Zend\Navigation\Service\AbstractNavigationFactory;

/**
 * Default navigation factory.
 */
class RightHandNavigationFactory extends AbstractNavigationFactory
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'right-sidebar';
    }
}
