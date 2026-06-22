<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessSiWithId;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
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

    public function testIsValidInternal(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted('INTERNAL_USER', true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidCanAccessSi(): void
    {
        //controller queries si with caseid
        //canaccesssiwithid checks if user is internal

        //canaccesssiwithid checks if si caseid matches caseid received
        $id = 1;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);
        $dto->shouldReceive('getCaseId')->andReturn(2);
    }

    public function testIsNotValidCannotAccessSi(): void
    {

    }
}
