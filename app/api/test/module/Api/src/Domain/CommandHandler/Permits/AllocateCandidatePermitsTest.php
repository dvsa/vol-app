<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\AllocateCandidatePermits;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

class AllocateCandidatePermitsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);
        $this->sut = new AllocateCandidatePermits();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpPermit::STATUS_PENDING
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $isShortTerm, mixed $expectedExpiryDate, mixed $expectedIssueDate): void
    {
        $irhpPermitApplicationId = 472;

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpPermitApplicationId);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->expects('isEcmtShortTerm')->withNoArgs()->andReturn($isShortTerm);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->expects('getIrhpApplication->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication->expects('generateExpiryDate')
            ->withNoArgs()
            ->times($isShortTerm ? 1 : 0)
            ->andReturn($expectedExpiryDate);

        $irhpPermitApplication->expects('generateIssueDate')
            ->withNoArgs()
            ->times($isShortTerm ? 1 : 0)
            ->andReturn($expectedIssueDate);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->with($irhpPermitApplicationId)
            ->andReturn($irhpPermitApplication);

        $range1 = $this->createIrhpPermitRange(400, 404, [400, 404]);
        $range2 = $this->createIrhpPermitRange(405, 409, [406, 407]);

        $candidatePermit1 = $this->createIrhpCandidatePermitMock($range1, $irhpPermitApplication);
        $candidatePermit2 = $this->createIrhpCandidatePermitMock($range2, $irhpPermitApplication);
        $candidatePermit3 = $this->createIrhpCandidatePermitMock($range1, $irhpPermitApplication);
        $candidatePermit4 = $this->createIrhpCandidatePermitMock($range2, $irhpPermitApplication);
        $candidatePermit5 = $this->createIrhpCandidatePermitMock($range1, $irhpPermitApplication);
        $candidatePermit6 = $this->createIrhpCandidatePermitMock($range2, $irhpPermitApplication);

        $successfulWantedCandidatePermits = [
            $candidatePermit1,
            $candidatePermit2,
            $candidatePermit3,
            $candidatePermit4,
            $candidatePermit5,
            $candidatePermit6
        ];

        $irhpPermitApplication->shouldReceive('getSuccessfulIrhpCandidatePermits')
            ->with(null, true)
            ->andReturn($successfulWantedCandidatePermits);

        $permitSaveCount = 0;

        $permitSaveExpectations = [
            [$candidatePermit1, $range1, $irhpPermitApplication, 401, false],
            [$candidatePermit2, $range2, $irhpPermitApplication, 405, false],
            [$candidatePermit3, $range1, $irhpPermitApplication, 402, false],
            [$candidatePermit4, $range2, $irhpPermitApplication, 408, false],
            [$candidatePermit5, $range1, $irhpPermitApplication, 403, false],
            [$candidatePermit6, $range2, $irhpPermitApplication, 409, false]
        ];

        $expectedStatus = $this->refData[IrhpPermit::STATUS_PENDING];

        $this->repoMap['IrhpPermit']->shouldReceive('save')
            ->with(m::on(function ($irhpPermit) use (&$permitSaveExpectations, &$permitSaveCount, $expectedExpiryDate, $expectedStatus, $expectedIssueDate) {
                foreach ($permitSaveExpectations as &$expectation) {
                    if (
                        ($irhpPermit->getIrhpCandidatePermit() === $expectation[0]) &&
                        ($irhpPermit->getIrhpPermitRange() === $expectation[1]) &&
                        ($irhpPermit->getIrhpPermitApplication() === $expectation[2]) &&
                        ($irhpPermit->getPermitNumber() == $expectation[3]) &&
                        ($irhpPermit->getExpiryDate() == $expectedExpiryDate) &&
                        ($expectedIssueDate === null || $irhpPermit->getIssueDate() == $expectedIssueDate) &&
                        ($irhpPermit->getStatus() == $expectedStatus)
                    ) {
                        $expectation[4] = true;
                    }
                }
                $permitSaveCount++;
                return true;
            }));

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(6, $permitSaveCount);
        foreach ($permitSaveExpectations as $expectation) {
            $this->assertTrue($expectation[4]);
        }

        $this->assertEquals(
            ['IRHP permit records created'],
            $result->getMessages()
        );

        $this->assertEquals(
            $irhpPermitApplicationId,
            $result->getId('irhpPermitApplication')
        );
    }

    public static function dpHandleCommand(): array
    {
        $expectedIssueDate = new \DateTime();
        $expectedIssueDate->add(new \DateInterval('P10D'));
        return [
            [true, new \DateTime(), $expectedIssueDate], //short term permit type, generates expiry date
            [false, null, null], //not short term so no expiry date generated
        ];
    }

    private function createIrhpCandidatePermitMock(mixed $irhpPermitRange, mixed $irhpPermitApplication): mixed
    {
        $irhpCandidatePermit = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange);
        $irhpCandidatePermit->shouldReceive('getIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);

        return $irhpCandidatePermit;
    }

    private function createIrhpPermitRange(mixed $fromNo, mixed $toNo, mixed $existingPermitNumbers): mixed
    {
        $irhpPermitRange = new IrhpPermitRange();

        // we'd have to set these props using reflection or some other mechanism if/when we remove the setters from the
        // generated abstract classes
        $irhpPermitRange->setFromNo($fromNo);
        $irhpPermitRange->setToNo($toNo);

        foreach ($existingPermitNumbers as $permitNumber) {
            $permit = m::mock(IrhpPermit::class);
            $permit->shouldReceive('getPermitNumber')
                ->andReturn($permitNumber);
            $irhpPermitRange->addIrhpPermits($permit);
        }

        return $irhpPermitRange;
    }
}
