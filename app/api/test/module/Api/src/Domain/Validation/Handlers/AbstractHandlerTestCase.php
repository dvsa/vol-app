<?php

declare(strict_types=1);

/**
 * Abstract Handler Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers;

use Dvsa\OlcsTest\Api\Domain\Validation\ValidationHelperTestCaseTrait;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Abstract Handler Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractHandlerTestCase extends MockeryTestCase
{
    use ValidationHelperTestCaseTrait;

    /**
     * @var AbstractHandler
     */
    protected $sut;
}
