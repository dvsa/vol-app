<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Pi;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Pi\Pi as Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as PiHearingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * Pi Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PiEntityTest extends EntityTester
{
    public function setUp(): void
    {
        /** @var \Dvsa\Olcs\Api\Entity\Pi\Pi entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * test create
     */
    public function testCreate(): void
    {
        $caseEntity = m::mock(CasesEntity::class);
        $caseEntity->shouldReceive('isClosed')->andReturn(false);
        $agreedByTc = m::mock(PresidingTcEntity::class);
        $agreedByTcRole = m::mock(RefData::class);
        $piTypes = new ArrayCollection();
        $reasons = new ArrayCollection();
        $agreedDate = m::mock(\DateTime::class);
        $piStatus = m::mock(RefData::class);
        $comment = 'comment';
        $isEcmsCase = false;
        $assignedCaseworker = m::mock(User::class);
        $ecmsFirstReceivedDate = new \DateTime();

        $pi = new Entity(
            $caseEntity,
            $agreedByTc,
            $agreedByTcRole,
            $piTypes,
            $reasons,
            $agreedDate,
            $piStatus,
            $comment,
            $isEcmsCase,
            $assignedCaseworker,
            $ecmsFirstReceivedDate
        );

        $this->assertEquals($caseEntity, $pi->getCase());
        $this->assertEquals($agreedByTc, $pi->getAgreedByTc());
        $this->assertEquals($agreedByTcRole, $pi->getAgreedByTcRole());
        $this->assertEquals($piTypes, $pi->getPiTypes());
        $this->assertEquals($reasons, $pi->getReasons());
        $this->assertEquals($agreedDate, $pi->getAgreedDate());
        $this->assertEquals($piStatus, $pi->getPiStatus());
        $this->assertEquals($comment, $pi->getComment());
        $this->assertEquals($assignedCaseworker, $pi->getAssignedCaseworker());
        $this->assertEquals($isEcmsCase, $pi->getIsEcmsCase());
        $this->assertEquals($ecmsFirstReceivedDate, $pi->getEcmsFirstReceivedDate());
    }

    /**
     * test create throws exception when case is closed
     */
    public function testCreateClosedCaseException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $caseEntity = m::mock(CasesEntity::class);
        $caseEntity->shouldReceive('isClosed')->andReturn(true);
        $agreedByTc = m::mock(PresidingTcEntity::class);
        $agreedByTcRole = m::mock(RefData::class);
        $assignedCaseworker = m::mock(User::class);
        $isEcmsCase = false;
        $ecmsFirstReceivedDate = new \DateTime();
        $piTypes = new ArrayCollection();
        $reasons = new ArrayCollection();
        $agreedDate = m::mock(\DateTime::class);
        $piStatus = m::mock(RefData::class);

        new Entity(
            $caseEntity,
            $agreedByTc,
            $agreedByTcRole,
            $piTypes,
            $reasons,
            $agreedDate,
            $piStatus,
            '',
            $isEcmsCase,
            $assignedCaseworker,
            $ecmsFirstReceivedDate
        );
    }

    /**
     * test agreed and legislation
     */
    public function testAgreedAndLegislation(): void
    {
        $agreedByTc = m::mock(PresidingTcEntity::class);
        $agreedByTcRole = m::mock(RefData::class);
        $assignedCaseworker = m::mock(User::class);
        $isEcmsCase = false;
        $ecmsFirstReceivedDate = new \DateTime();
        $piTypes = new ArrayCollection();
        $reasons = new ArrayCollection();
        $agreedDate = m::mock(\DateTime::class);
        $comment = 'comment';

        $this->entity->updateAgreedAndLegislation(
            $agreedByTc,
            $agreedByTcRole,
            $piTypes,
            $reasons,
            $agreedDate,
            $comment,
            $isEcmsCase,
            $assignedCaseworker,
            $ecmsFirstReceivedDate
        );

        $this->assertEquals($agreedByTc, $this->entity->getAgreedByTc());
        $this->assertEquals($agreedByTcRole, $this->entity->getAgreedByTcRole());
        $this->assertEquals($assignedCaseworker, $this->entity->getAssignedCaseworker());
        $this->assertEquals($isEcmsCase, $this->entity->getIsEcmsCase());
        $this->assertEquals($ecmsFirstReceivedDate, $this->entity->getEcmsFirstReceivedDate());
        $this->assertEquals($piTypes, $this->entity->getPiTypes());
        $this->assertEquals($reasons, $this->entity->getReasons());
        $this->assertEquals($agreedDate, $this->entity->getAgreedDate());
        $this->assertEquals($comment, $this->entity->getComment());
    }


    /**
     * test agreed and legislation accepts null for assignedCaseworker and ecmsFirstReceivedDate
     */
    public function testAgreedAndLegislationNullValues(): void
    {
        $agreedByTc = m::mock(PresidingTcEntity::class);
        $agreedByTcRole = m::mock(RefData::class);
        $assignedCaseworker = null;
        $isEcmsCase = true;
        $ecmsFirstReceivedDate = null;
        $piTypes = new ArrayCollection();
        $reasons = new ArrayCollection();
        $agreedDate = m::mock(\DateTime::class);
        $comment = 'comment';

        $this->entity->updateAgreedAndLegislation(
            $agreedByTc,
            $agreedByTcRole,
            $piTypes,
            $reasons,
            $agreedDate,
            $comment,
            $isEcmsCase,
            $assignedCaseworker,
            $ecmsFirstReceivedDate
        );

        $this->assertEquals($agreedByTc, $this->entity->getAgreedByTc());
        $this->assertEquals($agreedByTcRole, $this->entity->getAgreedByTcRole());
        $this->assertEquals($assignedCaseworker, $this->entity->getAssignedCaseworker());
        $this->assertEquals($isEcmsCase, $this->entity->getIsEcmsCase());
        $this->assertEquals($ecmsFirstReceivedDate, $this->entity->getEcmsFirstReceivedDate());
        $this->assertEquals($piTypes, $this->entity->getPiTypes());
        $this->assertEquals($reasons, $this->entity->getReasons());
        $this->assertEquals($agreedDate, $this->entity->getAgreedDate());
        $this->assertEquals($comment, $this->entity->getComment());
    }
    /**
     * test agreed and legislation throws exception when Pi is closed
     */
    public function testAgreedAndLegislationClosedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $agreedByTc = m::mock(PresidingTcEntity::class);
        $agreedByTcRole = m::mock(RefData::class);
        $assignedCaseworker = m::mock(User::class);
        $isEcmsCase = false;
        $ecmsFirstReceivedDate = new \DateTime();
        $piTypes = new ArrayCollection();
        $reasons = new ArrayCollection();
        $agreedDate = m::mock(\DateTime::class);
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->updateAgreedAndLegislation(
            $agreedByTc,
            $agreedByTcRole,
            $piTypes,
            $reasons,
            $agreedDate,
            '',
            $isEcmsCase,
            $assignedCaseworker,
            $ecmsFirstReceivedDate
        );
    }

    /**
     * @dataProvider dateProvider
     *
     * @param string $inputDate
     * @param \DateTime|null $entityDate
     */
    public function testUpdatePiWithDecision($inputDate, $entityDate): void
    {
        $decidedByTc = m::mock(PresidingTcEntity::class);
        $decidedByTcRole = m::mock(RefData::class);
        $decisions = new ArrayCollection();
        $licenceRevokedAtPi = 'Y';
        $licenceSuspendedAtPi = 'N';
        $licenceCurtailedAtPi = 'Y';
        $witnesses = 5;
        $decisionDate = $inputDate;
        $notificationDate = $inputDate;
        $decisionNotes = 'decision notes';
        $tmCalledWithOperator = 'Y';
        $tmDecisions = new ArrayCollection(['tmDdecision1', 'tmDecision2']);

        $this->entity->updatePiWithDecision(
            $decidedByTc,
            $decidedByTcRole,
            $decisions,
            $licenceRevokedAtPi,
            $licenceSuspendedAtPi,
            $licenceCurtailedAtPi,
            $witnesses,
            $decisionDate,
            $notificationDate,
            $decisionNotes,
            $tmCalledWithOperator,
            $tmDecisions
        );

        $this->assertEquals($decidedByTc, $this->entity->getDecidedByTc());
        $this->assertEquals($decidedByTcRole, $this->entity->getDecidedByTcRole());
        $this->assertEquals($decisions, $this->entity->getDecisions());
        $this->assertEquals($licenceRevokedAtPi, $this->entity->getLicenceRevokedAtPi());
        $this->assertEquals($licenceSuspendedAtPi, $this->entity->getLicenceSuspendedAtPi());
        $this->assertEquals($licenceCurtailedAtPi, $this->entity->getLicenceCurtailedAtPi());
        $this->assertEquals($witnesses, $this->entity->getWitnesses());
        $this->assertEquals($entityDate, $this->entity->getDecisionDate());
        $this->assertEquals($entityDate, $this->entity->getNotificationDate());
        $this->assertEquals($decisionNotes, $this->entity->getDecisionNotes());
        $this->assertEquals($tmDecisions, $this->entity->getTmDecisions());
        $this->assertEquals($tmCalledWithOperator, $this->entity->getTmCalledWithOperator());
    }

    /**
     * @dataProvider dataProviderTestUpdatePiWithDecisionDecisionBeforeHearing
     *
     * @param $expectException
     * @param $decisionDate
     * @param $hearingDate
     */
    public function testUpdatePiWithDecisionDecisionBeforeHearing($expectException, $decisionDate, $hearingDate): void
    {
        $piHearing = m::mock(PiHearingEntity::class);
        $piHearing->shouldReceive('getIsAdjourned')->andReturn('N');
        $piHearing->shouldReceive('getIsCancelled')->andReturn('N');
        $piHearing->shouldReceive('getHearingDate')->with(true)->andReturn($hearingDate);

        $this->entity->addPiHearings($piHearing);

        try {
            $this->entity->updatePiWithDecision(
                null,
                m::mock(RefData::class),
                new ArrayCollection(),
                null,
                null,
                null,
                null,
                $decisionDate,
                null,
                null,
                null,
                new ArrayCollection()
            );
            if ($expectException) {
                $this->fail('ValidationException should have been thrown');
            }

            // Assert the entity was successfully updated when no exception expected
            $this->assertInstanceOf(Entity::class, $this->entity);
        } catch (ValidationException $e) {
            if (!$expectException) {
                $this->fail('ValidationException should NOT have been thrown');
            }
            $this->assertSame(
                ['DECISION_DATE_BEFORE_HEARING_DATE' => $hearingDate->format('Y-m-d')],
                $e->getMessages()
            );
        }
    }

    public function dataProviderTestUpdatePiWithDecisionDecisionBeforeHearing(): array
    {
        return [
            [false, null, null],
            [false, '2010-10-10', null],
            [false, null, '2010-10-10'],
            [false, '2010-10-10', new \DateTime('2010-10-10')],
            [false, '2017-02-20', new \DateTime('2010-10-10')],
            'Same date, but hearing time 00:30' => [false, '2010-10-10', new \DateTime('2010-10-10 00:30')],
            'Same date, but hearing time 23:30' => [false, '2010-10-10', new \DateTime('2010-10-10 23:30')],
            'Decision date before hearing date' => [true, '2010-02-09', new \DateTime('2010-02-10')],
            'Decision date before hearing date by years' => [true, '2010-02-10', new \DateTime('2017-10-10')],
        ];
    }

    public function testUpdatePiWithDecisionClosedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $decidedByTc = m::mock(PresidingTcEntity::class);
        $decidedByTcRole = m::mock(RefData::class);
        $decisions = new ArrayCollection();
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->updatePiWithDecision(
            $decidedByTc,
            $decidedByTcRole,
            $decisions,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            new ArrayCollection()
        );
    }

    /**
     * @dataProvider dateProvider
     *
     * @param string $inputDate
     * @param \DateTime|null $entityDate
     */
    public function testUpdateWrittenOutcomeNone($inputDate, $entityDate): void
    {
        $writtenOutcome = m::mock(RefData::class);
        $callUpLetterDate = $inputDate;
        $briefToTcDate = $inputDate;

        $this->entity->updateWrittenOutcomeNone($writtenOutcome, $callUpLetterDate, $briefToTcDate);

        $this->assertEquals($writtenOutcome, $this->entity->getWrittenOutcome());
        $this->assertEquals($entityDate, $this->entity->getCallUpLetterDate());
        $this->assertEquals($entityDate, $this->entity->getBriefToTcDate());
        $this->assertEquals(null, $this->entity->getTcWrittenDecisionDate());
        $this->assertEquals(null, $this->entity->getDecisionLetterSentDate());
        $this->assertEquals(null, $this->entity->getTcWrittenReasonDate());
        $this->assertEquals(null, $this->entity->getWrittenReasonLetterDate());
    }

    public function testUpdateWrittenOutcomeNoneClosedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $writtenOutcome = m::mock(RefData::class);
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->updateWrittenOutcomeNone($writtenOutcome, null, null);
    }

    /**
     * @dataProvider dateProvider
     *
     * @param string $inputDate
     * @param \DateTime|null $entityDate
     */
    public function testUpdateWrittenOutcomeVerbalDecision($inputDate, $entityDate): void
    {
        $writtenOutcome = m::mock(RefData::class);
        $callUpLetterDate = $inputDate;
        $briefToTcDate = $inputDate;
        $decisionLetterSentDate = $inputDate;

        $this->entity->updateWrittenOutcomeVerbal(
            $writtenOutcome,
            $callUpLetterDate,
            $briefToTcDate,
            $decisionLetterSentDate
        );

        $this->assertEquals($writtenOutcome, $this->entity->getWrittenOutcome());
        $this->assertEquals($entityDate, $this->entity->getCallUpLetterDate());
        $this->assertEquals($entityDate, $this->entity->getBriefToTcDate());
        $this->assertEquals(null, $this->entity->getTcWrittenDecisionDate());
        $this->assertEquals($entityDate, $this->entity->getDecisionLetterSentDate());
        $this->assertEquals(null, $this->entity->getTcWrittenReasonDate());
        $this->assertEquals(null, $this->entity->getWrittenReasonLetterDate());
    }

    public function testUpdateWrittenOutcomeVerbalClosedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $writtenOutcome = m::mock(RefData::class);
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->updateWrittenOutcomeVerbal($writtenOutcome, null, null, null);
    }

    /**
     * @dataProvider dateProvider
     *
     * @param string $inputDate
     * @param \DateTime|null $entityDate
     */
    public function testUpdateWrittenOutcomeDecision($inputDate, $entityDate): void
    {
        $writtenOutcome = m::mock(RefData::class);
        $callUpLetterDate = $inputDate;
        $briefToTcDate = $inputDate;
        $tcWrittenDecisionDate = $inputDate;
        $decisionLetterSentDate = $inputDate;

        $this->entity->updateWrittenOutcomeDecision(
            $writtenOutcome,
            $callUpLetterDate,
            $briefToTcDate,
            $tcWrittenDecisionDate,
            $decisionLetterSentDate
        );

        $this->assertEquals($writtenOutcome, $this->entity->getWrittenOutcome());
        $this->assertEquals($entityDate, $this->entity->getCallUpLetterDate());
        $this->assertEquals($entityDate, $this->entity->getBriefToTcDate());
        $this->assertEquals($entityDate, $this->entity->getTcWrittenDecisionDate());
        $this->assertEquals(null, $this->entity->getDecisionLetterSentDate());
        $this->assertEquals(null, $this->entity->getTcWrittenReasonDate());
        $this->assertEquals(null, $this->entity->getWrittenReasonLetterDate());
    }

    public function testUpdateWrittenOutcomeDecisionClosedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $writtenOutcome = m::mock(RefData::class);
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->updateWrittenOutcomeDecision($writtenOutcome, null, null, null, null);
    }

    /**
     * @dataProvider dateProvider
     *
     * @param string $inputDate
     * @param \DateTime|null $entityDate
     */
    public function testUpdateWrittenOutcomeReason($inputDate, $entityDate): void
    {
        $writtenOutcome = m::mock(RefData::class);
        $callUpLetterDate = $inputDate;
        $briefToTcDate = $inputDate;
        $tcWrittenReasonDate = $inputDate;
        $writtenReasonLetterDate = $inputDate;

        $this->entity->updateWrittenOutcomeReason(
            $writtenOutcome,
            $callUpLetterDate,
            $briefToTcDate,
            $tcWrittenReasonDate,
            $writtenReasonLetterDate
        );

        $this->assertEquals($writtenOutcome, $this->entity->getWrittenOutcome());
        $this->assertEquals($entityDate, $this->entity->getCallUpLetterDate());
        $this->assertEquals($entityDate, $this->entity->getBriefToTcDate());
        $this->assertEquals(null, $this->entity->getTcWrittenDecisionDate());
        $this->assertEquals(null, $this->entity->getDecisionLetterSentDate());
        $this->assertEquals($entityDate, $this->entity->getTcWrittenReasonDate());
        $this->assertEquals($entityDate, $this->entity->getWrittenReasonLetterDate());
    }

    public function testUpdateWrittenOutcomeReasonClosedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $writtenOutcome = m::mock(RefData::class);
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->updateWrittenOutcomeReason($writtenOutcome, null, null, null, null);
    }

    /**
     * @dataProvider canCloseWithHearingProvider
     *
     * @param $isCancelled
     * @param $closedDate
     * @param $returnValue
     */
    public function testCanCloseWithHearing($isCancelled, $closedDate, $returnValue): void
    {
        $piHearing = m::mock(PiHearingEntity::class);
        $piHearing->shouldReceive('getIsCancelled')->andReturn($isCancelled);
        $piHearings = new ArrayCollection([$piHearing]);

        $writtenOutcome = m::mock(RefData::class);
        $writtenOutcome->shouldReceive('getId')->andReturn(null);

        $this->entity->setClosedDate($closedDate);
        $this->entity->setPiHearings($piHearings);
        $this->entity->setWrittenOutcome($writtenOutcome);

        $this->assertEquals($returnValue, $this->entity->canClose());
    }

    public function canCloseWithHearingProvider(): array
    {
        $date = '2015-12-25';

        return [
            ['Y', null, true],
            ['Y', $date, false],
            ['N', $date, false]
        ];
    }

    public function testCanCloseNoHearingNoOutcome(): void
    {
        $this->entity->setPiHearings(new ArrayCollection());
        $this->entity->setCallUpLetterDate(new \DateTime());
        $this->entity->setBriefToTcDate(new \DateTime());

        $this->assertEquals(false, $this->entity->canClose());
    }

    /**
     * @dataProvider canCloseWithOutcomeProvider
     */
    public function testCanCloseWithOutcome(
        $writtenOutcomeId,
        $tcWrittenReasonDate,
        $writtenReasonLetterDate,
        $tcWrittenDecisionDate,
        $writtenDecisionLetterDate,
        $decisionLetterSentDate,
        $closedDate,
        $returnValue
    ): void {
        $writtenOutcome = m::mock(RefData::class);
        $writtenOutcome->shouldReceive('getId')->andReturn($writtenOutcomeId);
        $this->entity->setClosedDate($closedDate);
        $this->entity->setPiHearings(new ArrayCollection());
        $this->entity->setCallUpLetterDate(new \DateTime());
        $this->entity->setBriefToTcDate(new \DateTime());
        $this->entity->setWrittenOutcome($writtenOutcome);
        $this->entity->setTcWrittenReasonDate($tcWrittenReasonDate);
        $this->entity->setWrittenReasonLetterDate($writtenReasonLetterDate);
        $this->entity->setTcWrittenDecisionDate($tcWrittenDecisionDate);
        $this->entity->setWrittenDecisionLetterDate($writtenDecisionLetterDate);
        $this->entity->setDecisionLetterSentDate($decisionLetterSentDate);

        $this->assertEquals($returnValue, $this->entity->canClose());
    }

    public function canCloseWithOutcomeProvider(): array
    {
        $date = '2015-12-25';

        return [
            [SlaEntity::VERBAL_DECISION_ONLY, null, null, null, null, null, null, false],
            [SlaEntity::VERBAL_DECISION_ONLY, null, null, null, null, $date, null, true],
            [SlaEntity::VERBAL_DECISION_ONLY, null, null, null, null, $date, $date, false],
            [SlaEntity::WRITTEN_OUTCOME_DECISION, null, null, null, null, null, null, false],
            [SlaEntity::WRITTEN_OUTCOME_DECISION, null, null, $date, $date, null, null, true],
            [SlaEntity::WRITTEN_OUTCOME_DECISION, null, null, $date, $date, null, $date, false],
            [SlaEntity::WRITTEN_OUTCOME_DECISION, null, null, $date, null, null, null, false],
            [SlaEntity::WRITTEN_OUTCOME_DECISION, null, null, null, $date, null, null, false],
            [SlaEntity::WRITTEN_OUTCOME_REASON, null, null, null, null, null, null, false],
            [SlaEntity::WRITTEN_OUTCOME_REASON, $date, $date, null, null, null, null, true],
            [SlaEntity::WRITTEN_OUTCOME_REASON, $date, $date, null, null, null, $date, false],
            [SlaEntity::WRITTEN_OUTCOME_REASON, $date, null, null, null, null, null, false],
            [SlaEntity::WRITTEN_OUTCOME_REASON, null, $date, null, null, null, null, false]
        ];
    }

    /**
     * @dataProvider canCloseWithMissingGeneralSlaProvider
     *
     * @param $briefToTcDate
     * @param $callUpLetterDate
     */
    public function testCanCloseWithMissingGeneralSla($briefToTcDate, $callUpLetterDate): void
    {
        $this->entity->setPiHearings(new ArrayCollection());
        $this->entity->setCallUpLetterDate($briefToTcDate);
        $this->entity->setBriefToTcDate($callUpLetterDate);

        $this->assertEquals(false, $this->entity->canClose());
    }

    public function canCloseWithMissingGeneralSlaProvider(): array
    {
        $date = '2015-12-25';

        return [
            [null, $date],
            [$date, null],
        ];
    }

    /**
     * Tests closing a Pi
     */
    public function testClose(): void
    {
        $piHearing = m::mock(PiHearingEntity::class);
        $piHearing->shouldReceive('getIsCancelled')->andReturn('Y');

        $this->entity->setPiHearings(new ArrayCollection([$piHearing]));

        $this->entity->close();

        $this->assertInstanceOf('\DateTime', $this->entity->getClosedDate());
    }

    public function testCloseThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $this->entity->setPiHearings(new ArrayCollection());

        $this->entity->close();
    }

    /**
     * Tests reopen
     */
    public function testReopen(): void
    {
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->reopen();

        $this->assertEquals(null, $this->entity->getClosedDate());
    }

    public function testReopenThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $this->entity->setPiHearings(new ArrayCollection());

        $this->entity->reopen();
    }


    /**
     * @dataProvider dpGetHearingDateProvider
     *
     * @param string $hearingDate
     * @param string $isAdjourned
     * @param string $isCancelled
     * @param string|null $returnValue
     */
    public function testGetHearingDate($hearingDate, $isAdjourned, $isCancelled, $returnValue): void
    {
        $piHearing = m::mock(PiHearingEntity::class);
        $piHearing->shouldReceive('getHearingDate')->andReturn($hearingDate);
        $piHearing->shouldReceive('getIsAdjourned')->andReturn($isAdjourned);
        $piHearing->shouldReceive('getIsCancelled')->andReturn($isCancelled);

        $this->entity->setPiHearings(new ArrayCollection([$piHearing]));

        $this->assertEquals($returnValue, $this->entity->getHearingDate());
    }

    /**
     * @return array
     */
    public function dpGetHearingDateProvider(): array
    {
        $date = '2015-12-25';

        return [
            [$date, 'Y', 'Y', null],
            [$date, 'N', 'Y', null],
            [$date, 'Y', 'N', null],
            [$date, 'N', 'N', $date],
        ];
    }

    /**
     * Tests getCalculatedBundleValues
     */
    public function testGetCalculatedBundleValues(): void
    {
        $isTm = true;

        $cases = m::mock(CasesEntity::class);
        $cases->shouldReceive('isTm')->andReturn($isTm);

        $this->entity->setCase($cases);
        $this->entity->setPiHearings(new ArrayCollection());

        $expected = [
            'isClosed' => false,
            'canReopen' => false,
            'canClose' => false,
            'hearingDate' => null,
            'isTm' => $isTm
        ];

        $this->assertEquals($expected, $this->entity->getCalculatedBundleValues());
    }

    /**
     * @dataProvider isTmProvider
     */
    public function testIsTm(bool $isTm): void
    {
        //test with no case attached
        $this->entity->setCase(null);
        $this->assertFalse($this->entity->isTm());

        $case = m::mock(CasesEntity::class);
        $case->shouldReceive('isTm')->andReturn($isTm);
        $this->entity->setCase($case);

        $this->assertEquals($isTm, $this->entity->isTm());
    }

    public function isTmProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * provider to check dates are processed properly
     *
     * @return array
     */
    public function dateProvider(): array
    {
        $date = '2015-12-25';

        return [
            ['invalid date', null],
            [$date, \DateTime::createFromFormat('Y-m-d', $date)->setTime(0, 0, 0)]
        ];
    }

    /**
     * Tests flattenSlaTargetDates
     */
    public function testFlattenSlaTargetDates(): void
    {
        $date1 = new \DateTime('2015-03-14');

        $sla1 = m::mock(SlaTargetDateEntity::class);
        $sla1->shouldReceive('getField')->andReturn('field1');

        $slaTargetDate1 = m::mock(SlaTargetDateEntity::class);
        $slaTargetDate1->shouldReceive('getSla')->andReturn($sla1);
        $slaTargetDate1->shouldReceive('getTargetDate')->andReturn($date1);

        $date2 = new \DateTime('2015-03-14');

        $sla2 = m::mock(SlaTargetDateEntity::class);
        $sla2->shouldReceive('getField')->andReturn('field2');

        $slaTargetDate2 = m::mock(SlaTargetDateEntity::class);
        $slaTargetDate2->shouldReceive('getSla')->andReturn($sla2);
        $slaTargetDate2->shouldReceive('getTargetDate')->andReturn($date2);

        $this->entity->addSlaTargetDates($slaTargetDate1);
        $this->entity->addSlaTargetDates($slaTargetDate2);

        $expected = [
            'field1Target' => $date1,
            'field2Target' => $date2,
        ];

        $this->assertEquals($expected, $this->entity->flattenSlaTargetDates());
    }
}
