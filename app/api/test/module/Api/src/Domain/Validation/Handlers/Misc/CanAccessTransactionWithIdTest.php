<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessTransactionWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessTransactionWithId
 */
class CanAccessTransactionWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessTransactionWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTransactionWithId();

        parent::setUp();
    }

    /**
     *
     * @param $expected
     * @param $isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValid(mixed $expected, mixed $isValid): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessTransaction', [76], $isValid);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function dataProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
