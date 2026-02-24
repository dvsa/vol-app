<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class IrhpPermitWindowTraitTest extends AbstractCommandHandlerTestCase
{
    /** @var IrhpPermitWindowTraitStub $sut */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IrhpPermitWindowTraitStub();

        parent::setUp();
    }

    public function testValidateStockRangesNonBilateral(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType->isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->validateStockRanges($irhpPermitStock);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidateStockRangesBilateral')]
    public function testValidateStockRangesBilateral(
        mixed $isBilateralCabotageOnly,
        mixed $isBilateralStandardOnly,
        mixed $isBilateralStandardAndCabotage,
        mixed $hasCabotageRange,
        mixed $hasStandardRange,
        mixed $expectedErr
    ): void {
        $applicationPathGroup = m::mock(ApplicationPathGroup::class);
        $applicationPathGroup->shouldReceive('isBilateralCabotageOnly')
            ->withNoArgs()
            ->andReturn($isBilateralCabotageOnly)
            ->shouldReceive('isBilateralStandardOnly')
            ->withNoArgs()
            ->andReturn($isBilateralStandardOnly)
            ->shouldReceive('isBilateralStandardAndCabotage')
            ->withNoArgs()
            ->andReturn($isBilateralStandardAndCabotage);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType->isBilateral')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpPermitStock->shouldReceive('getApplicationPathGroup')
            ->withNoArgs()
            ->andReturn($applicationPathGroup)
            ->shouldReceive('hasCabotageRange')
            ->withNoArgs()
            ->andReturn($hasCabotageRange)
            ->shouldReceive('hasStandardRange')
            ->withNoArgs()
            ->andReturn($hasStandardRange);

        if (isset($expectedErr)) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage($expectedErr);
        }

        $this->sut->validateStockRanges($irhpPermitStock);
    }

    public static function dpValidateStockRangesBilateral(): array
    {
        return
            [
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => false,
                    'expectedErr' => null,
                ],
                // isBilateralCabotageOnly
                [
                    'isBilateralCabotageOnly' => true,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => false,
                    'expectedErr' => 'ERR_IRHP_BIL_CAB_ONLY_STOCK_WITHOUT_CAB_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => true,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => true,
                    'hasStandardRange' => false,
                    'expectedErr' => null,
                ],
                [
                    'isBilateralCabotageOnly' => true,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => true,
                    'hasStandardRange' => true,
                    'expectedErr' => 'ERR_IRHP_BIL_CAB_ONLY_STOCK_WITH_STD_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => true,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => true,
                    'expectedErr' => 'ERR_IRHP_BIL_CAB_ONLY_STOCK_WITHOUT_CAB_RANGE_WITH_STD_RANGE',
                ],
                // isBilateralStandardOnly
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => true,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => false,
                    'expectedErr' => 'ERR_IRHP_BIL_STD_ONLY_STOCK_WITHOUT_STD_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => true,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => true,
                    'hasStandardRange' => false,
                    'expectedErr' => 'ERR_IRHP_BIL_STD_ONLY_STOCK_WITHOUT_STD_RANGE_WITH_CAB_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => true,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => true,
                    'hasStandardRange' => true,
                    'expectedErr' => 'ERR_IRHP_BIL_STD_ONLY_STOCK_WITH_CAB_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => true,
                    'isBilateralStandardAndCabotage' => false,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => true,
                    'expectedErr' => null,
                ],
                // isBilateralStandardAndCabotage
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => true,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => false,
                    'expectedErr' => 'ERR_IRHP_BIL_STD_AND_CAB_STOCK_WITHOUT_STD_RANGE_WITHOUT_CAB_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => true,
                    'hasCabotageRange' => true,
                    'hasStandardRange' => false,
                    'expectedErr' => 'ERR_IRHP_BIL_STD_AND_CAB_STOCK_WITHOUT_STD_RANGE',
                ],
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => true,
                    'hasCabotageRange' => true,
                    'hasStandardRange' => true,
                    'expectedErr' => null,
                ],
                [
                    'isBilateralCabotageOnly' => false,
                    'isBilateralStandardOnly' => false,
                    'isBilateralStandardAndCabotage' => true,
                    'hasCabotageRange' => false,
                    'hasStandardRange' => true,
                    'expectedErr' => 'ERR_IRHP_BIL_STD_AND_CAB_STOCK_WITH_CAB_RANGE',
                ],
            ];
    }
}
