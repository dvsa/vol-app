<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanEditBusRegWithId;

/**
 * Can Edit Bus Reg With Id Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanEditBusRegWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanEditBusRegWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanEditBusRegWithId();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canEdit, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);

        $this->setIsValid('canEditBusReg', [111], $canEdit);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
