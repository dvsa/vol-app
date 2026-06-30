<?php

declare(strict_types=1);

/**
 * Standard Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Standard;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

/**
 * Standard Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StandardTest extends MockeryTestCase
{
    /**
     * @var Standard
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new Standard();

        Logger::setLogger(new \Psr\Log\NullLogger());
    }

    public function testIsValid(): void
    {
        $dto = m::mock();

        $this->assertTrue($this->sut->isValid($dto));
    }
}
