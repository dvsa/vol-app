<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessBusRegWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessBusRegWithId
 */
class CanAccessBusRegWithIdTest extends AbstractHandlerTestCase
{
    /** @var CanAccessBusRegWithId */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessBusRegWithId();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        $id = 1111;

        /** @var CommandInterface | m\MockInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);

        $this->setIsValid('canAccessBusReg', [$id], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
