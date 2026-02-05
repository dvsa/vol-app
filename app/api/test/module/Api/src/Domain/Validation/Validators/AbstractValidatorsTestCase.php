<?php

declare(strict_types=1);

/**
 * Abstract Validators Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\OlcsTest\Api\Domain\Validation\ValidationHelperTestCaseTrait;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Abstract Validators Test Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractValidatorsTestCase extends MockeryTestCase
{
    use ValidationHelperTestCaseTrait;

    protected $sut;
}
