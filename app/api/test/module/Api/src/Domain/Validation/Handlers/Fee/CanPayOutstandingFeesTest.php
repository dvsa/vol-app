<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Fee;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Fee\CanPayOutstandingFees;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Fee\CanPayOutstandingFees
 */
class CanPayOutstandingFeesTest extends AbstractHandlerTestCase
{
    /**
     * @var CanPayOutstandingFees
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanPayOutstandingFees();

        parent::setUp();
    }

    /**
     *
     * @param $expected
     * @param $isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValidOrganisation(mixed $expected, mixed $isValid): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisationId')->andReturn('34');

        $this->setIsValid('canAccessOrganisation', [34], $isValid);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     *
     * @param $expected
     * @param $isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValidApplication(mixed $expected, mixed $isValid): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisationId')->andReturn(null);
        $dto->shouldReceive('getApplicationId')->andReturn(34);

        $this->setIsValid('canAccessApplication', [34], $isValid);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     *
     * @param $expected
     * @param $isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValidIrhpApplication(mixed $expected, mixed $isValid): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisationId')->andReturn(null);
        $dto->shouldReceive('getApplicationId')->andReturn(null);
        $dto->shouldReceive('getIrhpApplication')->andReturn(2);

        $this->setIsValid('canAccessIrhpApplicationWithId', [2], $isValid);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     *
     * @param $expected
     * @param $isValid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testIsValidFees(mixed $expected, mixed $isValid): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisationId')->andReturn(null);
        $dto->shouldReceive('getApplicationId')->andReturn(null);
        $dto->shouldReceive('getIrhpApplication')->andReturn(null);
        $dto->shouldReceive('getFeeIds')->andReturn([34, 56]);

        $this->setIsValid('canAccessFee', [34], $isValid);
        $this->setIsValid('canAccessFee', [56], $isValid);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function testIsValidFeesMixed(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisationId')->andReturn(null);
        $dto->shouldReceive('getApplicationId')->andReturn(null);
        $dto->shouldReceive('getIrhpApplication')->andReturn(null);
        $dto->shouldReceive('getFeeIds')->andReturn([34, 56, 76]);

        $this->setIsValid('canAccessFee', [34], false);
        $this->setIsValid('canAccessFee', [56], true);
        $this->setIsValid('canAccessFee', [76], true);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public function testIsValidNoContext(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisationId')->andReturn(null);
        $dto->shouldReceive('getApplicationId')->andReturn(null);
        $dto->shouldReceive('getIrhpApplication')->andReturn(null);
        $dto->shouldReceive('getFeeIds')->andReturn(null);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public static function dataProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
