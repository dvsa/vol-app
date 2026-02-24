<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\Controller\ControllerTestTrait;

/**
 * Helper functions for testing LVA controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractLvaControllerTestCase extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager(): mixed
    {
        return Bootstrap::getServiceManager();
    }
}
