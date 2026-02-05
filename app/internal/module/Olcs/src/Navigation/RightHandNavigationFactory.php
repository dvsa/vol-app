<?php

namespace Olcs\Navigation;

use Laminas\Navigation\Service\AbstractNavigationFactory;

/**
 * Default navigation factory.
 */
class RightHandNavigationFactory extends AbstractNavigationFactory
{
    /**
     * @return string
     */
    #[\Override]
    public function getName()
    {
        return 'right-sidebar';
    }
}
