<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitRange;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitRange\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as PermitRangeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Create IRHP Permit Range Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('Country', CountryRepo::class);
        $this->mockRepo('IrhpPermitRange', PermitRangeRepo::class);
        $this->mockRepo('IrhpPermitStock', PermitStockRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            RefData::EMISSIONS_CATEGORY_EURO5_REF,
            RefData::EMISSIONS_CATEGORY_EURO6_REF,
            RefData::EMISSIONS_CATEGORY_NA_REF,
            RefData::JOURNEY_SINGLE,
        ];

        $this->references = [
            Country::class => [
                Country::ID_FRANCE => m::mock(Country::class),
            ]
        ];

        parent::initReferences();
    }

    /**
     * Test the Happy Path
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpShortTermAnnualTypeCombinations')]
    public function testHandleCommand(mixed $isEcmtShortTerm, mixed $isEcmtAnnual, mixed $isBilateral): void
    {
        $cmdData = [
            'irhpPermitStock' => '1',
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
            'prefix' => 'UK',
            'fromNo' => '1',
            'toNo' => '100',
            'isReserve' => '0',
            'isReplacement' => '0',
            'countrys' => [Country::ID_FRANCE],
            'journey' => RefData::JOURNEY_SINGLE,
            'cabotage' => '0',
        ];

        $command = CreateCmd::create($cmdData);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')->andReturn($isEcmtAnnual);
        $irhpPermitType->shouldReceive('isBilateral')->andReturn($isBilateral);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType')->andReturn($irhpPermitType);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitRange']->shouldReceive('findOverlappingRangesByType')
            ->with(
                $cmdData['irhpPermitStock'],
                $cmdData['prefix'],
                $cmdData['fromNo'],
                $cmdData['toNo'],
                null
            )
            ->andReturn([]);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PermitRangeEntity::class))
            ->andReturnUsing(
                function (PermitRangeEntity $permitRange) {
                    $permitRange->setId(1);

                    $this->assertEquals(
                        [$this->references[Country::class][Country::ID_FRANCE]],
                        $permitRange->getCountrys()
                    );
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpPermitRange' => 1],
            'messages' => ["IRHP Permit Range '1' created"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
    * Test for overlapping IRHP Permit Ranges - no values are asserted as this tests to ensure that a validation
    * exception is thrown.
    */
    public function testHandleOverlap(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $cmdData = [
            'irhpPermitStock' => '1',
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF,
            'prefix' => 'UK',
            'fromNo' => '1',
            'toNo' => '100',
            'isReserve' => '0',
            'isReplacement' => '0',
            'countrys' => [],
            'journey' => RefData::JOURNEY_SINGLE,
            'cabotage' => '0',
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->andReturn(m::mock(IrhpPermitStock::class));

        $this->repoMap['IrhpPermitRange']->shouldReceive('findOverlappingRangesByType')
            ->with(
                $cmdData['irhpPermitStock'],
                $cmdData['prefix'],
                $cmdData['fromNo'],
                $cmdData['toNo'],
                null
            )
            ->andReturn(['overlappingPermitRange']);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandBilateralNoJourney(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $cmdData = [
            'irhpPermitStock' => '1',
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF,
            'prefix' => 'UK',
            'fromNo' => '1',
            'toNo' => '100',
            'isReserve' => '0',
            'isReplacement' => '0',
            'countrys' => [],
            'cabotage' => '0',
        ];

        $command = CreateCmd::create($cmdData);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')->andReturn(false);
        $irhpPermitType->shouldReceive('isEcmtAnnual')->andReturn(false);
        $irhpPermitType->shouldReceive('isBilateral')->andReturn(true);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType')->andReturn($irhpPermitType);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitRange']->shouldReceive('findOverlappingRangesByType')
            ->with(
                $cmdData['irhpPermitStock'],
                $cmdData['prefix'],
                $cmdData['fromNo'],
                $cmdData['toNo'],
                null
            )
            ->andReturn([]);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpShortTermAnnualTypeCombinations')]
    public function testHandleCommandBadEcmtEmissionsCategory(mixed $isEcmtShortTerm, mixed $isEcmtAnnual, mixed $isBilateral): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $cmdData = [
            'irhpPermitStock' => '1',
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_NA_REF,
            'prefix' => 'UK',
            'fromNo' => '1',
            'toNo' => '100',
            'isReserve' => '0',
            'isReplacement' => '0',
            'countrys' => [],
            'journey' => RefData::JOURNEY_SINGLE,
            'cabotage' => '0',
        ];

        $command = CreateCmd::create($cmdData);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')->andReturn($isEcmtAnnual);
        $irhpPermitType->shouldReceive('isBilateral')->andReturn($isBilateral);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType')->andReturn($irhpPermitType);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitRange']->shouldReceive('findOverlappingRangesByType')
            ->with(
                $cmdData['irhpPermitStock'],
                $cmdData['prefix'],
                $cmdData['fromNo'],
                $cmdData['toNo'],
                null
            )
            ->andReturn([]);

        $this->sut->handleCommand($command);
    }

    public static function dpShortTermAnnualTypeCombinations(): array
    {
        return [
            [true, false, false],
            [false, true, false],
        ];
    }
}
