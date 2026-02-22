<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\StockBasedForCpProviderFactory;
use Dvsa\Olcs\Cli\Domain\Command\Permits\ApplyRangesToSuccessfulPermitApplications as ApplyRangesToSuccessfulPermitApplicationsCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\ApplyRangesToSuccessfulPermitApplications as ApplyRangesToSuccessfulPermitApplicationsHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Apply ranges to successful permit applications test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplyRangesToSuccessfulPermitApplicationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(ApplyRangesToSuccessfulPermitApplicationsHandler::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);
        $this->mockRepo('IrhpPermitRange', IrhpPermitRangeRepo::class);
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        $this->mockedSmServices = [
           'PermitsApplyRangesStockBasedForCpProviderFactory' => m::mock(StockBasedForCpProviderFactory::class)
        ];

        parent::setUp();
    }

    /**
     * tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $stockId = 8;

        $ranges = [
            [
                'id' => 1,
                'size' => 10,
                'fromNo' => 100,
                'permitCount' => 5,
                'countryCodes' => ['RU', 'IT'],
                'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF
            ],
            [
                'id' => 2,
                'size' => 15,
                'fromNo' => 200,
                'permitCount' => 12,
                'countryCodes' => [],
                'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF
            ],
            [
                'id' => 3,
                'size' => 5,
                'fromNo' => 300,
                'permitCount' => 4,
                'countryCodes' => ['IT'],
                'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF
            ],
        ];

        $irhpPermitRanges = [];
        $irhpPermitRangesByRangeId = [];

        foreach ($ranges as $range) {
            $irhpPermitRange = $this->createRange(
                $range['id'],
                $range['size'],
                $range['fromNo'],
                $range['countryCodes'],
                $range['emissionsCategory']
            );

            $irhpPermitRanges[] = $irhpPermitRange;
            $irhpPermitRangesByRangeId[$range['id']] = $irhpPermitRange;

            $this->repoMap['IrhpPermit']->shouldReceive('getPermitCountByRange')
                ->with($range['id'])
                ->andReturn($range['permitCount']);
        }

        $this->repoMap['IrhpPermitRange']->shouldReceive('getByStockId')
            ->with($stockId)
            ->andReturn($irhpPermitRanges);

        $candidatePermits = [
            ['id' => 1, 'selectedRange' => 1, 'randomizedScore' => '0.99'],
            ['id' => 2, 'selectedRange' => 2, 'randomizedScore' => '0.98'],
            ['id' => 3, 'selectedRange' => 3, 'randomizedScore' => '0.97'],
            ['id' => 4, 'selectedRange' => 1, 'randomizedScore' => '0.96'],
            ['id' => 5, 'selectedRange' => 2, 'randomizedScore' => '0.95'],
            ['id' => 6, 'selectedRange' => 1, 'randomizedScore' => '0.94'],
            ['id' => 7, 'selectedRange' => 2, 'randomizedScore' => '0.93'],
            ['id' => 8, 'selectedRange' => 1, 'randomizedScore' => '0.92'],
            ['id' => 9, 'selectedRange' => 1, 'randomizedScore' => '0.91'],
        ];

        $forCpProvider = m::mock(ForCpProvider::class);

        $this->mockedSmServices['PermitsApplyRangesStockBasedForCpProviderFactory']->shouldReceive('create')
            ->with($stockId)
            ->andReturn($forCpProvider);

        $irhpCandidatePermits = [];
        foreach ($candidatePermits as $candidatePermit) {
            $irhpCandidatePermit = $this->createCandidatePermit(
                $candidatePermit['id'],
                $candidatePermit['randomizedScore']
            );

            $selectedIrhpPermitRange = $irhpPermitRangesByRangeId[$candidatePermit['selectedRange']];

            $forCpProvider->shouldReceive('selectRange')
                ->with(m::type(Result::class), $irhpCandidatePermit, m::type('array'))
                ->andReturn($selectedIrhpPermitRange);

            $irhpCandidatePermit->shouldReceive('applyRange')
                ->with($selectedIrhpPermitRange)
                ->once()
                ->globally()
                ->ordered();

            $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
                ->with($irhpCandidatePermit)
                ->once()
                ->globally()
                ->ordered();

            $irhpCandidatePermits[] = $irhpCandidatePermit;
        }

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulScoreOrderedInScope')
            ->with($stockId)
            ->andReturn($irhpCandidatePermits);

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('flushAll')
            ->once()
            ->globally()
            ->ordered();

        $expectedMessages = [
            '  - processing candidate permit 1 with randomised score 0.99',
            '    - assigned range id 1 to candidate permit 1',
            '    - decrementing stock in range 1, stock is now 4',
            '  - processing candidate permit 2 with randomised score 0.98',
            '    - assigned range id 2 to candidate permit 2',
            '    - decrementing stock in range 2, stock is now 2',
            '  - processing candidate permit 3 with randomised score 0.97',
            '    - assigned range id 3 to candidate permit 3',
            '    - decrementing stock in range 3, stock is now 0',
            '    - stock in range 3 is now exhausted',
            '  - processing candidate permit 4 with randomised score 0.96',
            '    - assigned range id 1 to candidate permit 4',
            '    - decrementing stock in range 1, stock is now 3',
            '  - processing candidate permit 5 with randomised score 0.95',
            '    - assigned range id 2 to candidate permit 5',
            '    - decrementing stock in range 2, stock is now 1',
            '  - processing candidate permit 6 with randomised score 0.94',
            '    - assigned range id 1 to candidate permit 6',
            '    - decrementing stock in range 1, stock is now 2',
            '  - processing candidate permit 7 with randomised score 0.93',
            '    - assigned range id 2 to candidate permit 7',
            '    - decrementing stock in range 2, stock is now 0',
            '    - stock in range 2 is now exhausted',
            '  - processing candidate permit 8 with randomised score 0.92',
            '    - assigned range id 1 to candidate permit 8',
            '    - decrementing stock in range 1, stock is now 1',
            '  - processing candidate permit 9 with randomised score 0.91',
            '    - assigned range id 1 to candidate permit 9',
            '    - decrementing stock in range 1, stock is now 0',
            '    - stock in range 1 is now exhausted',
        ];

        $result = $this->sut->handleCommand(
            ApplyRangesToSuccessfulPermitApplicationsCommand::create(['stockId' => $stockId])
        );

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }

    private function createRange(mixed $id, mixed $size, mixed $fromNo, mixed $countryCodes, mixed $emissionsCategoryId): mixed
    {
        $persistentCollection = $this->createCountriesPersistentCollection($countryCodes);

        $range = m::mock(IrhpPermitRange::class);
        $range->shouldReceive('getId')
            ->andReturn($id);
        $range->shouldReceive('getSize')
            ->andReturn($size);
        $range->shouldReceive('getFromNo')
            ->andReturn($fromNo);
        $range->shouldReceive('getCountrys')
            ->andReturn($persistentCollection);
        $range->shouldReceive('getEmissionsCategory->getId')
            ->andReturn($emissionsCategoryId);

        return $range;
    }

    private function createCandidatePermit(mixed $id, mixed $randomizedScore): mixed
    {
        $candidatePermit = m::mock(IrhpCandidatePermit::class);
        $candidatePermit->shouldReceive('getId')
            ->andReturn($id);
        $candidatePermit->shouldReceive('getRandomizedScore')
            ->andReturn($randomizedScore);

        return $candidatePermit;
    }

    private function createCountriesPersistentCollection(mixed $countryCodes): mixed
    {
        $countries = [];
        foreach ($countryCodes as $countryCode) {
            $country = m::mock(Country::class);
            $country->shouldReceive('getId')
                ->andReturn($countryCode);
            $countries[] = $country;
        }

        $persistentCollection = m::mock(AbstractLazyCollection::class);
        $persistentCollection->shouldReceive('getValues')
            ->andReturn($countries);

        return $persistentCollection;
    }
}
