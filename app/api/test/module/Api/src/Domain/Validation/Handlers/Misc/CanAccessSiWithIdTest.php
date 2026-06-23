<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessSiWithId;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class CanAccessSiWithIdTest extends AbstractHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessSiWithId();

        parent::setUp();
    }

    public function testIsValidInternalNoContext(): void
    {
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(1);
        $dto->shouldReceive('getCaseId')->andReturn(null);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $si = m::mock();
        $this->mockRepo('SeriousInfringement')->shouldReceive('fetchById')->with(1)->andReturn($si);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValidNotInternalUser(): void
    {
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWhenSiBelongsToCase(): void
    {
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(1);
        $dto->shouldReceive('getCaseId')->andReturn(2);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $case = m::mock();
        $case->shouldReceive('getId')->andReturn(2);

        $si = m::mock();
        $si->shouldReceive('getCase')->andReturn($case);

        $this->mockRepo('SeriousInfringement')->shouldReceive('fetchById')->with(1)->andReturn($si);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValidWhenSiDoesNotBelongToCase(): void
    {
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(1);
        $dto->shouldReceive('getCaseId')->andReturn(2);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $case = m::mock();
        $case->shouldReceive('getId')->andReturn(999);

        $si = m::mock();
        $si->shouldReceive('getCase')->andReturn($case);

        $this->mockRepo('SeriousInfringement')->shouldReceive('fetchById')->with(1)->andReturn($si);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsNotValidWhenSiHasNoCase(): void
    {
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(1);
        $dto->shouldReceive('getCaseId')->andReturn(2);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $si = m::mock();
        $si->shouldReceive('getCase')->andReturn(null);

        $this->mockRepo('SeriousInfringement')->shouldReceive('fetchById')->with(1)->andReturn($si);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
