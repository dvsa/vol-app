<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessSiWithId;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\Cases\Si\Si as SiQuery;
use Mockery as m;

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
        $dto = m::mock(SiQuery::class);
        $dto->shouldReceive('getCase')->once()->andReturn(null);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValidNotInternalUser(): void
    {
        $dto = m::mock(SiQuery::class);
        $dto->shouldReceive('getCase')->once()->andReturn(null);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWhenSiBelongsToCase(): void
    {
        $dto = m::mock(SiQuery::class);
        $dto->shouldReceive('getId')->andReturn(1);
        $dto->shouldReceive('getCase')->once()->andReturn(2);

        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsValid('seriousInfringementBelongsToCase', [1, 2], true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValidWhenSiDoesNotBelongToCase(): void
    {
        $dto = m::mock(SiQuery::class);
        $dto->shouldReceive('getId')->andReturn(1);
        $dto->shouldReceive('getCase')->once()->andReturn(2);

        $this->setIsValid('seriousInfringementBelongsToCase', [1, 2], false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
