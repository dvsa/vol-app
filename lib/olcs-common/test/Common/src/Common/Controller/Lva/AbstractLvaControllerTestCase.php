<?php

namespace CommonTest\Common\Controller\Lva;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Helper functions for testing LVA controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractLvaControllerTestCase extends MockeryTestCase
{
    protected function getServiceManager()
    {
        $sm = m::mock(\Laminas\ServiceManager\ServiceManager::class)
            ->makePartial()
            ->setAllowOverride(true);

        // inject a real string helper
        $sm->setService('Helper\String', new \Common\Service\Helper\StringHelperService());

        return $sm;
    }
}
