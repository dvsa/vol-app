<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as IrhpPermitSectorQuotaRepo;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulSectorPermitApplications as MarkSuccessfulSectorPermitApplicationsCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\MarkSuccessfulSectorPermitApplications as MarkSuccessfulSectorPermitApplicationsHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Mark Successful Sector Permit Applications test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulSectorPermitApplicationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new MarkSuccessfulSectorPermitApplicationsHandler();
        $this->mockRepo('IrhpPermitSectorQuota', IrhpPermitSectorQuotaRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsScoringSuccessfulCandidatePermitsFacade' => m::mock(SuccessfulCandidatePermitsFacade::class)
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $stockId = 8;

        $this->repoMap['IrhpPermitSectorQuota']->shouldReceive('fetchByNonZeroQuota')
            ->with($stockId)
            ->andReturn(
                [
                    ['sectorId' => 7, 'quotaNumber' => 4],
                    ['sectorId' => 3, 'quotaNumber' => 2],
                    ['sectorId' => 2, 'quotaNumber' => 3]
                ]
            );

        $candidatePermitsInSectorId7 = [
            ['id' => 4, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 5, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('getScoreOrderedBySectorInScope')
            ->with($stockId, 7)
            ->andReturn($candidatePermitsInSectorId7);

        $successfulCandidatePermitsInSectorId7 = [
            ['id' => 4, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 5, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('generate')
            ->with($candidatePermitsInSectorId7, $stockId, 4)
            ->once()
            ->ordered()
            ->andReturn($successfulCandidatePermitsInSectorId7);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('log')
            ->with($successfulCandidatePermitsInSectorId7, m::type(Result::class))
            ->once()
            ->ordered();

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('write')
            ->with($successfulCandidatePermitsInSectorId7)
            ->once()
            ->ordered();

        $candidatePermitsInSectorId3 = [
            ['id' => 10, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 13, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 15, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 16, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('getScoreOrderedBySectorInScope')
            ->with($stockId, 3)
            ->andReturn($candidatePermitsInSectorId3);

        $successfulCandidatePermitsInSectorId3 = [
            ['id' => 10, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 13, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
        ];

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('generate')
            ->with($candidatePermitsInSectorId3, $stockId, 2)
            ->once()
            ->ordered()
            ->andReturn($successfulCandidatePermitsInSectorId3);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('log')
            ->with($successfulCandidatePermitsInSectorId3, m::type(Result::class))
            ->once()
            ->ordered();

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('write')
            ->with($successfulCandidatePermitsInSectorId3)
            ->once()
            ->ordered();

        $candidatePermitsInSectorId2 = [];

        $this->repoMap['IrhpApplication']->shouldReceive('getScoreOrderedBySectorInScope')
            ->with($stockId, 2)
            ->andReturn($candidatePermitsInSectorId2);

        $successfulCandidatePermitsInSectorId2 = [];

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('generate')
            ->with($candidatePermitsInSectorId2, $stockId, 3)
            ->once()
            ->ordered()
            ->andReturn($successfulCandidatePermitsInSectorId2);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('log')
            ->with($successfulCandidatePermitsInSectorId2, m::type(Result::class))
            ->once()
            ->ordered();

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('write')
            ->with($successfulCandidatePermitsInSectorId2)
            ->once()
            ->ordered();

        $expectedMessages = [
            'STEP 2b:',
            '  Sectors associated with stock where quota > 0: 3',
            '    Sector with id 7:',
            '      Derived values:',
            '      - #sectorQuota: 4',
            '      Permits requesting this sector: 2',
            '      - adjusted for quota: 2',
            '    Sector with id 3:',
            '      Derived values:',
            '      - #sectorQuota: 2',
            '      Permits requesting this sector: 4',
            '      - adjusted for quota: 2',
            '    Sector with id 2:',
            '      Derived values:',
            '      - #sectorQuota: 3',
            '      Permits requesting this sector: 0',
            '      - adjusted for quota: 0',
            '  4 permits have been marked as successful'
        ];

        $result = $this->sut->handleCommand(
            MarkSuccessfulSectorPermitApplicationsCommand::create(['stockId' => $stockId])
        );

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }
}
