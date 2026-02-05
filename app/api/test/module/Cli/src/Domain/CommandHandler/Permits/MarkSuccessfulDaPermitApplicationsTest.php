<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as IrhpPermitJurisdictionQuotaRepo;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulDaPermitApplications as MarkSuccessfulDaPermitApplicationsCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\MarkSuccessfulDaPermitApplications as MarkSuccessfulDaPermitApplicationsHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Mark Successful DA Permit Applications test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulDaPermitApplicationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new MarkSuccessfulDaPermitApplicationsHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitJurisdictionQuota', IrhpPermitJurisdictionQuotaRepo::class);

        $this->mockedSmServices = [
            'PermitsScoringSuccessfulCandidatePermitsFacade' => m::mock(SuccessfulCandidatePermitsFacade::class)
        ];

        parent::setUp();
    }

    /**
     * tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $stockId = 8;

        $this->repoMap['IrhpPermitJurisdictionQuota']->shouldReceive('fetchByNonZeroQuota')
            ->with($stockId)
            ->andReturn(
                [
                    ['jurisdictionId' => 4, 'quotaNumber' => 10],
                    ['jurisdictionId' => 6, 'quotaNumber' => 4],
                    ['jurisdictionId' => 8, 'quotaNumber' => 15]
                ]
            );

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulDaCountInScope')
            ->with($stockId, 4)
            ->andReturn(9);
        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulDaCountInScope')
            ->with($stockId, 6)
            ->andReturn(6);
        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulDaCountInScope')
            ->with($stockId, 8)
            ->andReturn(13);

        $candidatePermitsInJurisdictionId4 = [
            ['id' => 4, RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 5, RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 6, RefData::EMISSIONS_CATEGORY_EURO5_REF]
        ];

        $successfulCandidatePermitsInJurisdictionId4 = [
            ['id' => 4, RefData::EMISSIONS_CATEGORY_EURO6_REF]
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('getUnsuccessfulScoreOrderedInScope')
            ->with($stockId, 4)
            ->andReturn($candidatePermitsInJurisdictionId4);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('generate')
            ->with($candidatePermitsInJurisdictionId4, $stockId, 1)
            ->once()
            ->ordered()
            ->andReturn($successfulCandidatePermitsInJurisdictionId4);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('log')
            ->with($successfulCandidatePermitsInJurisdictionId4, m::type(Result::class))
            ->once();

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('write')
            ->with($successfulCandidatePermitsInJurisdictionId4)
            ->once()
            ->ordered();

        $candidatePermitsInJurisdictionId8 = [
            ['id' => 12, RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 14, RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 20, RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 25, RefData::EMISSIONS_CATEGORY_EURO5_REF]
        ];

        $successfulCandidatePermitsInJurisdictionId8 = [
            ['id' => 12, RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 20, RefData::EMISSIONS_CATEGORY_EURO5_REF]
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('getUnsuccessfulScoreOrderedInScope')
            ->with($stockId, 8)
            ->andReturn($candidatePermitsInJurisdictionId8);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('generate')
            ->with($candidatePermitsInJurisdictionId8, $stockId, 2)
            ->once()
            ->ordered()
            ->andReturn($successfulCandidatePermitsInJurisdictionId8);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('log')
            ->with($successfulCandidatePermitsInJurisdictionId8, m::type(Result::class))
            ->once();

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('write')
            ->with($successfulCandidatePermitsInJurisdictionId8)
            ->once()
            ->ordered();

        $expectedMessages = [
            'STEP 2c:',
            '  DAs associated with stock where quota > 0: 3',
            '    DA with id 4:',
            '      Derived values:',
            '      - #DAQuota:          10',
            '      - #DASuccessCount:   9',
            '      - #DARemainingQuota: 1',
            '      Permits requesting this DA: 3',
            '      - adjusted for quota: 1',
            '    DA with id 6:',
            '      Derived values:',
            '      - #DAQuota:          4',
            '      - #DASuccessCount:   6',
            '      - #DARemainingQuota: -2',
            '      #DARemainingQuota < 1 - nothing to do',
            '    DA with id 8:',
            '      Derived values:',
            '      - #DAQuota:          15',
            '      - #DASuccessCount:   13',
            '      - #DARemainingQuota: 2',
            '      Permits requesting this DA: 4',
            '      - adjusted for quota: 2',
            '  3 permits have been marked as successful'
        ];

        $result = $this->sut->handleCommand(
            MarkSuccessfulDaPermitApplicationsCommand::create(['stockId' => $stockId])
        );

        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
