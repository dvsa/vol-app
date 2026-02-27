<?php

declare(strict_types=1);

/**
 * No Validation Required Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;

/**
 * IsSideEffect Test
 */
class IsSideEffectTest extends AbstractHandlerTestCase
{
    /**
     * @var NoValidationRequired
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsSideEffect();

        parent::setUp();
    }

    public function testIsValid(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
