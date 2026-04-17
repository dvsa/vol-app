<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Query\Permits\DeviationData as DeviationDataQuery;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Cli\Domain\Command\Permits\InitialiseScope as InitialiseScopeCommand;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\InitialiseScope as InitialiseScopeHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Initialise Scope test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class InitialiseScopeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(InitialiseScopeHandler::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $stockId = 7;

        $candidatePermitId1 = 5;
        $candidatePermitId2 = 8;
        $candidatePermitId3 = 11;

        $candidatePermit1LicNo = 123456;
        $candidatePermit3LicNo = 123456;

        $deviationSourceValues = [
            [
                'candidatePermitId' => $candidatePermitId1,
                'applicationId' => 1,
                'licNo' => $candidatePermit1LicNo,
                'permitsRequired' => 12
            ],
            [
                'candidatePermitId' => $candidatePermitId2,
                'applicationId' => 2,
                'licNo' => 455123,
                'permitsRequired' => 6
            ],
            [
                'candidatePermitId' => $candidatePermitId3,
                'applicationId' => 1,
                'licNo' => $candidatePermit3LicNo,
                'permitsRequired' => 12
            ],
        ];

        $deviationData = [
            'licenceData' => [
                '123456' => [1 => '12'],
                '455123' => [2 => '6']
            ],
            'meanDeviation' => 1.5
        ];

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(DeviationDataQuery::class))
            ->andReturnUsing(function ($query) use ($deviationSourceValues, $deviationData) {
                $this->assertEquals($deviationSourceValues, $query->getSourceValues());
                return $deviationData;
            });

        $this->repoMap['IrhpApplication']->shouldReceive('clearScope')
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('applyScope')
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchDeviationSourceValues')
            ->with($stockId)
            ->andReturn($deviationSourceValues);

        $candidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchById')
            ->with($candidatePermitId1)
            ->andReturn($candidatePermit1);
        $candidatePermit1->shouldReceive('prepareForScoring')
            ->once();
        $candidatePermit1->shouldReceive('hasRandomizedScore')
            ->andReturn(false);
        $candidatePermit1->shouldReceive('applyRandomizedScore')
            ->with($deviationData, $candidatePermit1LicNo)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
            ->with($candidatePermit1)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchById')
            ->with($candidatePermitId2)
            ->andReturn($candidatePermit2);
        $candidatePermit2->shouldReceive('prepareForScoring')
            ->once();
        $candidatePermit2->shouldReceive('hasRandomizedScore')
            ->andReturn(true);

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
            ->with($candidatePermit2)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit3 = m::mock(IrhpCandidatePermit::class);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchById')
            ->with($candidatePermitId3)
            ->andReturn($candidatePermit3);
        $candidatePermit3->shouldReceive('prepareForScoring')
            ->once();
        $candidatePermit3->shouldReceive('hasRandomizedScore')
            ->andReturn(false);
        $candidatePermit3->shouldReceive('applyRandomizedScore')
            ->with($deviationData, $candidatePermit3LicNo)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
            ->with($candidatePermit3)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('flushAll')
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand(
            InitialiseScopeCommand::create(['stockId' => $stockId])
        );

        $expectedMessages = [
            'using computed mean deviation of 1.5',
            'Established scope of candidate permits',
            '    - Candidate permits in scope: 3',
            '    - Randomised scores set: 2'
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }

    public function testMeanDeviationOverride(): void
    {
        $stockId = 7;

        $candidatePermitId1 = 5;
        $candidatePermitId2 = 8;
        $candidatePermitId3 = 11;

        $candidatePermit1LicNo = 123456;
        $candidatePermit3LicNo = 123456;

        $deviationSourceValues = [
            [
                'candidatePermitId' => $candidatePermitId1,
                'applicationId' => 1,
                'licNo' => $candidatePermit1LicNo,
                'permitsRequired' => 12
            ],
            [
                'candidatePermitId' => $candidatePermitId2,
                'applicationId' => 2,
                'licNo' => 455123,
                'permitsRequired' => 6
            ],
            [
                'candidatePermitId' => $candidatePermitId3,
                'applicationId' => 1,
                'licNo' => $candidatePermit3LicNo,
                'permitsRequired' => 12
            ],
        ];

        $deviationData = [
            'licenceData' => [
                '123456' => [1 => '12'],
                '455123' => [2 => '6']
            ],
            'meanDeviation' => 1.5
        ];

        $deviationDataWithMeanDeviationOverride = [
            'licenceData' => [
                '123456' => [1 => '12'],
                '455123' => [2 => '6']
            ],
            'meanDeviation' => 2.5
        ];

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(DeviationDataQuery::class))
            ->andReturnUsing(function ($query) use ($deviationSourceValues, $deviationData) {
                $this->assertEquals($deviationSourceValues, $query->getSourceValues());
                return $deviationData;
            });

        $this->repoMap['IrhpApplication']->shouldReceive('clearScope')
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('applyScope')
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchDeviationSourceValues')
            ->with($stockId)
            ->andReturn($deviationSourceValues);

        $candidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchById')
            ->with($candidatePermitId1)
            ->andReturn($candidatePermit1);
        $candidatePermit1->shouldReceive('prepareForScoring')
            ->once();
        $candidatePermit1->shouldReceive('hasRandomizedScore')
            ->andReturn(false);
        $candidatePermit1->shouldReceive('applyRandomizedScore')
            ->with($deviationDataWithMeanDeviationOverride, $candidatePermit1LicNo)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
            ->with($candidatePermit1)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchById')
            ->with($candidatePermitId2)
            ->andReturn($candidatePermit2);
        $candidatePermit2->shouldReceive('prepareForScoring')
            ->once();
        $candidatePermit2->shouldReceive('hasRandomizedScore')
            ->andReturn(true);

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
            ->with($candidatePermit2)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit3 = m::mock(IrhpCandidatePermit::class);
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('fetchById')
            ->with($candidatePermitId3)
            ->andReturn($candidatePermit3);
        $candidatePermit3->shouldReceive('prepareForScoring')
            ->once();
        $candidatePermit3->shouldReceive('hasRandomizedScore')
            ->andReturn(false);
        $candidatePermit3->shouldReceive('applyRandomizedScore')
            ->with($deviationDataWithMeanDeviationOverride, $candidatePermit3LicNo)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('saveOnFlush')
            ->with($candidatePermit3)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('flushAll')
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand(
            InitialiseScopeCommand::create(
                [
                    'stockId' => $stockId,
                    'deviation' => 2.5,
                ]
            )
        );

        $expectedMessages = [
            'using manually overridden mean deviation of 2.5',
            'Established scope of candidate permits',
            '    - Candidate permits in scope: 3',
            '    - Randomised scores set: 2'
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }
}
