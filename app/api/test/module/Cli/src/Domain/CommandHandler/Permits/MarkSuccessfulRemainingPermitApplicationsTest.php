<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulRemainingPermitApplications as MarkSuccessfulRemainingPermitApplicationsCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\MarkSuccessfulRemainingPermitApplications as MarkSuccessfulRemainingPermitApplicationsHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Mark Successful Remaining Permit Applications test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulRemainingPermitApplicationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new MarkSuccessfulRemainingPermitApplicationsHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);
        $this->mockRepo('IrhpPermitRange', IrhpPermitRangeRepo::class);

        $this->mockedSmServices = [
            'PermitsScoringSuccessfulCandidatePermitsFacade' => m::mock(SuccessfulCandidatePermitsFacade::class)
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $permitCount = 79;
        $successfulCount = 67;
        $stockId = 8;
        $combinedRangeSize = 150;

        $underConsiderationCandidatePermits = [
            ['id' => 13, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 17, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 41, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 46, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 55, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 61, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 80, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];

        $successfulCandidatePermits = [
            ['id' => 13, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 17, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO5_REF],
            ['id' => 46, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
            ['id' => 55, 'emissions_category' => RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];

        $this->repoMap['IrhpPermitRange']->shouldReceive('getCombinedRangeSize')
            ->with($stockId)
            ->andReturn($combinedRangeSize);

        $this->repoMap['IrhpPermit']->shouldReceive('getPermitCount')
            ->with($stockId)
            ->andReturn($permitCount);

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulCountInScope')
            ->with($stockId)
            ->andReturn($successfulCount);

        $this->repoMap['IrhpApplication']->shouldReceive('getUnsuccessfulScoreOrderedInScope')
            ->with($stockId)
            ->andReturn($underConsiderationCandidatePermits);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('generate')
            ->with($underConsiderationCandidatePermits, $stockId, 4)
            ->once()
            ->andReturn($successfulCandidatePermits);

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('write')
            ->with($successfulCandidatePermits)
            ->once();

        $this->mockedSmServices['PermitsScoringSuccessfulCandidatePermitsFacade']->shouldReceive('log')
            ->with($successfulCandidatePermits, m::type(Result::class))
            ->once();

        $expectedMessages = [
            'STEP 2d:',
            '  Derived values:',
            '    - #availableStockCount: 150',
            '    - #validPermitCount:    79',
            '    - #allocationQuota:     71',
            '    - #successfulPACount:   67',
            '    - #remainingQuota:      4',
            '  Unsuccessful remaining permits found in stock: 7'
        ];

        $result = $this->sut->handleCommand(
            MarkSuccessfulRemainingPermitApplicationsCommand::create(['stockId' => $stockId])
        );

        $this->assertEquals($expectedMessages, $result->getMessages());
    }

    public function testHandleCommandZeroRemainingQuota(): void
    {
        $stockId = 8;

        $this->repoMap['IrhpPermitRange']->shouldReceive('getCombinedRangeSize')
            ->with($stockId)
            ->andReturn(150);

        $this->repoMap['IrhpPermit']->shouldReceive('getPermitCount')
            ->with($stockId)
            ->andReturn(75);

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulCountInScope')
            ->with($stockId)
            ->andReturn(75);

        $expectedMessages = [
            'STEP 2d:',
            '  Derived values:',
            '    - #availableStockCount: 150',
            '    - #validPermitCount:    75',
            '    - #allocationQuota:     75',
            '    - #successfulPACount:   75',
            '    - #remainingQuota:      0',
            '#remainingQuota < 1 - nothing to do'
        ];

        $result = $this->sut->handleCommand(
            MarkSuccessfulRemainingPermitApplicationsCommand::create(['stockId' => $stockId])
        );

        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
