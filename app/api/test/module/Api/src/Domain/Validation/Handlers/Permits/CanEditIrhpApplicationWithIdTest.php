<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanEditIrhpApplicationWithId;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanEditIrhpApplicationWithId
 */
class CanEditIrhpApplicationWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanEditIrhpApplicationWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanEditIrhpApplicationWithId();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsValid')]
    public function testIsValid(mixed $canEdit, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);

        $this->setIsValid('canEditIrhpApplicationWithId', [$id], $canEdit);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function dpTestIsValid(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
