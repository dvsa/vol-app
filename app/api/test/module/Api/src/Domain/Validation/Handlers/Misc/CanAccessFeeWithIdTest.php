<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessFeeWithId;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as FeeQuery;

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

    public function testIsValidInternalNoContext(): void
    {
        $id = 1;
        $dto = m::mock(FeeQuery::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $dto->shouldReceive('getLicence')->once()->andReturn(null);
        $dto->shouldReceive('getApplication')->once()->andReturn(null);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidCanAccessFeeNoContext(): void
    {
        $id = 1;
        $dto = m::mock(FeeQuery::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $dto->shouldReceive('getLicence')->once()->andReturn(null);
        $dto->shouldReceive('getApplication')->once()->andReturn(null);
        $this->setIsValid('canAccessFee', [$id], true);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValid(): void
    {
        $id = 1;
        $dto = m::mock(FeeQuery::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $dto->shouldReceive('getLicence')->once()->andReturn(null);
        $dto->shouldReceive('getApplication')->once()->andReturn(null);
        $this->setIsValid('canAccessFee', [$id], false);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWhenFeeBelongsToLicence(): void
    {
        $id = 1;
        $dto = m::mock(FeeQuery::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $dto->shouldReceive('getLicence')->once()->andReturn(212);
        $dto->shouldReceive('getApplication')->never();

        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsValid('feeBelongsToLicence', [$id, 212], true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValidWhenFeeDoesNotBelongToLicence(): void
    {
        $id = 1;
        $dto = m::mock(FeeQuery::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $dto->shouldReceive('getLicence')->once()->andReturn(212);
        $dto->shouldReceive('getApplication')->never();

        $this->setIsValid('feeBelongsToLicence', [$id, 212], false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
