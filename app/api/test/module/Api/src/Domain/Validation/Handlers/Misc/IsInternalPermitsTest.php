<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalPermits;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * IsInternalPermits Test
 */
class IsInternalPermitsTest extends AbstractHandlerTestCase
{
    /**
     * @var IsInternalPermits
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsInternalPermits();

        parent::setUp();
    }

    public function testIsValidInternalPermits(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_PERMITS, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidInternalPermitsFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_PERMITS, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
