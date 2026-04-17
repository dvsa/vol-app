<?php

declare(strict_types=1);

/**
 * Update candidate permit selection test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdateCandidatePermitSelection as Sut;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCandidatePermitSelection as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update candidate permit selection test
 */
class UpdateCandidatePermitSelectionTest extends AbstractCommandHandlerTestCase
{
    public const IRHP_APPLICATION_ID = 47;

    private $irhpApplication;

    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('FeeType', FeeTypeRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);

        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(self::IRHP_APPLICATION_ID)
            ->andReturn($this->irhpApplication);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $issueFeeProductReference = 'ISSUE_FEE_PRODUCT_REFERENCE';
        $licenceId = 70;

        $candidatePermit1Id = 20;
        $candidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($candidatePermit1Id);
        $candidatePermit1->shouldReceive('updateWanted')
            ->with(true)
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('save')
            ->with($candidatePermit1)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit2Id = 40;
        $candidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($candidatePermit2Id);
        $candidatePermit2->shouldReceive('updateWanted')
            ->with(false)
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('save')
            ->with($candidatePermit2)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermit3Id = 60;
        $candidatePermit3 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($candidatePermit3Id);
        $candidatePermit3->shouldReceive('updateWanted')
            ->with(true)
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpCandidatePermit']->shouldReceive('save')
            ->with($candidatePermit3)
            ->once()
            ->globally()
            ->ordered();

        $candidatePermits = [$candidatePermit1, $candidatePermit2, $candidatePermit3];

        $outstandingFee1Id = 100;
        $outstandingFee1 = m::mock(Fee::class);
        $outstandingFee1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($outstandingFee1Id);

        $outstandingFee2Id = 130;
        $outstandingFee2 = m::mock(Fee::class);
        $outstandingFee2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($outstandingFee2Id);

        $outstandingFees = [$outstandingFee1, $outstandingFee2];

        $this->expectedSideEffect(
            CancelFee::class,
            ['id' => $outstandingFee1Id],
            new Result()
        );

        $this->expectedSideEffect(
            CancelFee::class,
            ['id' => $outstandingFee2Id],
            new Result()
        );

        $issueFeeTypeId = 123;
        $issueFeeTypeDescription = 'Issue fee type description';
        $issueFeeType = m::mock(FeeType::class);
        $issueFeeType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($issueFeeTypeId);
        $issueFeeType->shouldReceive('getDescription')
            ->withNoArgs()
            ->andReturn($issueFeeTypeDescription);
        $issueFeeType->shouldReceive('getFixedValue')
            ->withNoArgs()
            ->andReturn(6);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($issueFeeProductReference)
            ->andReturn($issueFeeType);

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => $licenceId,
                'irhpApplication' => self::IRHP_APPLICATION_ID,
                'invoicedDate' => date('Y-m-d'),
                'description' => 'Issue fee type description - 2 permits',
                'feeType' => $issueFeeTypeId,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'amount' => 12,
            ],
            new Result()
        );

        $this->irhpApplication->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(self::IRHP_APPLICATION_ID);
        $this->irhpApplication->shouldReceive('canSelectCandidatePermits')
            ->withNoArgs()
            ->andReturnTrue();
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getSuccessfulIrhpCandidatePermits')
            ->withNoArgs()
            ->andReturn($candidatePermits);
        $this->irhpApplication->shouldReceive('getOutstandingFees')
            ->withNoArgs()
            ->andReturn($outstandingFees);
        $this->irhpApplication->shouldReceive('getIssueFeeProductReference')
            ->withNoArgs()
            ->andReturn($issueFeeProductReference);
        $this->irhpApplication->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId);

        $command = Cmd::create(
            [
                'id' => self::IRHP_APPLICATION_ID,
                'selectedCandidatePermitIds' => [
                    $candidatePermit1Id,
                    $candidatePermit3Id,
                    'junk string',
                    ['array element', new \stdClass()]
                ]
            ]
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCantSelectCandidatePermits(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Sut::ERR_CANT_SELECT_CANDIDATE_PERMITS);

        $this->irhpApplication->shouldReceive('canSelectCandidatePermits')
            ->withNoArgs()
            ->andReturnFalse();

        $command = Cmd::create(
            [
                'id' => self::IRHP_APPLICATION_ID,
                'selectedCandidatePermitIds' => [1, 2, 3]
            ]
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNoPermitsWanted(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Sut::ERR_NO_PERMITS_WANTED);

        $candidatePermit1Id = 20;
        $candidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($candidatePermit1Id);

        $candidatePermit2Id = 40;
        $candidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($candidatePermit2Id);

        $candidatePermits = [$candidatePermit1, $candidatePermit2];

        $this->irhpApplication->shouldReceive('canSelectCandidatePermits')
            ->withNoArgs()
            ->andReturnTrue();
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getSuccessfulIrhpCandidatePermits')
            ->withNoArgs()
            ->andReturn($candidatePermits);

        $command = Cmd::create(
            [
                'id' => self::IRHP_APPLICATION_ID,
                'selectedCandidatePermitIds' => [
                    10,
                    30,
                    'junk string',
                    ['array element', new \stdClass()]
                ]
            ]
        );

        $this->sut->handleCommand($command);
    }
}
