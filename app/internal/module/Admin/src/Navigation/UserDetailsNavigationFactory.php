<?php

namespace Admin\Navigation;

use Laminas\Navigation\Service\AbstractNavigationFactory;

/**
 * User details navigation factory.
 */
class UserDetailsNavigationFactory extends AbstractNavigationFactory
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'user-details';
    }
}
