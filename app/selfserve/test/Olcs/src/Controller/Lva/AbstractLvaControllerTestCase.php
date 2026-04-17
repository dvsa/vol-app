<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use OlcsTest\Bootstrap;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Controller\Traits\ControllerTestTrait;

/**
 * Helper functions for testing LVA controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractLvaControllerTestCase extends MockeryTestCase
{
    use ControllerTestTrait;
}
