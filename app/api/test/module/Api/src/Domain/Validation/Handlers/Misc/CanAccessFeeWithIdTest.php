<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessFeeWithId;

/**
 * Can access fee with id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessFeeWithIdTest extends AbstractHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessFeeWithId();

        parent::setUp();
    }

    public function testIsValidInternal(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidCanAccessFee(): void
    {
        $id = 1;
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $this->setIsValid('canAccessFee', [$id], true);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValid(): void
    {
        $id = 1;
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $this->setIsValid('canAccessFee', [$id], false);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
