<?php
namespace Olcs\Navigation;

use Laminas\Navigation\Service\AbstractNavigationFactory;

/**
 * Dashboard navigation factory.
 */
class DashboardNavigationFactory extends AbstractNavigationFactory
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'dashboard';
    }
}
