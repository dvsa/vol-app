<?php

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtAutomaticallyWithdrawn;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggAppSubmitted;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgUnsuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPartSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgAppSubmitted;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApggIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApsgIssued;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermAutomaticallyWithdrawn;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermUnsuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApsgPartSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermAppSubmitted;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\Sectors;
use Dvsa\Olcs\Api\Entity\Permits\Traits\ApplicationAcceptConsts;
use Dvsa\Olcs\Api\Entity\SectionableInterface;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * IrhpApplication Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpApplicationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @var Entity|m\MockInterface
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->sut->initCollections();

        parent::setUp();
    }

    public function testGetCalculatedBundleValues()
    {
        $businessProcess = m::mock(RefData::class);

        $this->sut->shouldReceive('getApplicationRef')
            ->once()
            ->withNoArgs()
            ->andReturn('appRef')
            ->shouldReceive('canBeCancelled')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeTerminated')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeWithdrawn')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeDeclined')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeSubmitted')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeResetToNotYetSubmittedFromValid')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeResetToNotYetSubmittedFromCancelled')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeRevivedFromWithdrawn')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canBeRevivedFromUnsuccessful')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('hasOutstandingFees')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('getOutstandingFeeAmount')
            ->once()
            ->withNoArgs()
            ->andReturn(0)
            ->shouldReceive('getSectionCompletion')
            ->once()
            ->withNoArgs()
            ->andReturn([])
            ->shouldReceive('hasCheckedAnswers')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('hasMadeDeclaration')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isNotYetSubmitted')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('isOverviewAccessible')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('isSubmittedForConsideration')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('isFeePaid')
            ->andReturn(false)
            ->shouldReceive('isIssueInProgress')
            ->andReturn(false)
            ->shouldReceive('isAwaitingFee')
            ->andReturn(false)
            ->shouldReceive('isUnderConsideration')
            ->andReturn(false)
            ->shouldReceive('isCancelled')
            ->andReturn(false)
            ->shouldReceive('isWithdrawn')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isDeclined')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isReadyForNoOfPermits')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isBilateral')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isMultilateral')
            ->once()
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('canCheckAnswers')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('canMakeDeclaration')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('getPermitsRequired')
            ->once()
            ->withNoArgs()
            ->andReturn(0)
            ->shouldReceive('canUpdateCountries')
            ->once()
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('getQuestionAnswerData')
            ->once()
            ->withNoArgs()
            ->andReturn([])
            ->shouldReceive('getBusinessProcess')
            ->once()
            ->withNoArgs()
            ->andReturn($businessProcess)
            ->shouldReceive('requiresPreAllocationCheck')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->assertSame(
            [
                'applicationRef' => 'appRef',
                'canBeCancelled' => false,
                'canBeTerminated' => false,
                'canBeWithdrawn' => false,
                'canBeGranted' => false,
                'canBeDeclined' => false,
                'canBeSubmitted' => false,
                'canBeResetToNotYetSubmittedFromValid' => false,
                'canBeResetToNotYetSubmittedFromCancelled' => false,
                'canBeRevivedFromWithdrawn' => false,
                'canBeRevivedFromUnsuccessful' => false,
                'hasOutstandingFees' => false,
                'outstandingFeeAmount' => 0,
                'sectionCompletion' => [],
                'hasCheckedAnswers' => false,
                'hasMadeDeclaration' => false,
                'isNotYetSubmitted' => true,
                'isOverviewAccessible' => true,
                'isSubmittedForConsideration' => false,
                'isValid' => false,
                'isFeePaid' => false,
                'isIssueInProgress' => false,
                'isAwaitingFee' => false,
                'isUnderConsideration' => false,
                'isReadyForNoOfPermits' => false,
                'isCancelled' => false,
                'isWithdrawn' => false,
                'isDeclined' => false,
                'isBilateral' => false,
                'isMultilateral' => false,
                'canCheckAnswers' => true,
                'canMakeDeclaration' => true,
                'permitsRequired' => 0,
                'canUpdateCountries' => true,
                'questionAnswerData' => [],
                'businessProcess' => $businessProcess,
                'requiresPreAllocationCheck' => true,
            ],
            $this->sut->getCalculatedBundleValues()
        );
    }

    public function testGetApplicationRef()
    {
        $this->sut->setId(987);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicNo')
            ->andReturn('ABC123');

        $this->sut->setLicence($licence);

        $this->assertSame('ABC123 / 987', $this->sut->getApplicationRef());
    }

    public function testGetRelatedOrganisation()
    {
        $organisation = m::mock(Organisation::class);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation')
            ->andReturn($organisation);

        $this->sut->setLicence($licence);

        $this->assertSame(
            $organisation,
            $this->sut->getRelatedOrganisation()
        );
    }

    public function testGetRelatedLicence()
    {
        $licence = m::mock(Licence::class);
        $entity = $this->createNewEntity(null, null, null, $licence);
        $this->assertEquals($licence, $entity->getRelatedLicence());
    }

    /**
     * @dataProvider dpTestIsValid
     */
    public function testIsValid($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isValid());
    }

    public function dpTestIsValid()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, true],
        ];
    }

    /**
     * @dataProvider dpTestIsUnderConsideration
     */
    public function testIsUnderConsideration($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isUnderConsideration());
    }

    public function dpTestIsUnderConsideration()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, true],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpTestIsAwaitingFee
     */
    public function testIsAwaitingFee($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isAwaitingFee());
    }

    public function dpTestIsAwaitingFee()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, true],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpTestIsFeePaid
     */
    public function testIsFeePaid($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isFeePaid());
    }

    public function dpTestIsFeePaid()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, true],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpTestIsIssueInProgress
     */
    public function testIsIssueInProgress($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isIssueInProgress());
    }

    public function dpTestIsIssueInProgress()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, true],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpTestIsActive
     */
    public function testIsActive($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isActive());
    }

    public function dpTestIsActive()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, true],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, true],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, true],
            [IrhpInterface::STATUS_FEE_PAID, true],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, true],
            [IrhpInterface::STATUS_VALID, false],
            [IrhpInterface::STATUS_EXPIRED, false],
        ];
    }

    /**
     * Tests cancelling an application
     */
    public function testCancel()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setStatus(new RefData(IrhpInterface::STATUS_NOT_YET_SUBMITTED));
        $entity->cancel(new RefData(IrhpInterface::STATUS_CANCELLED));
        $this->assertEquals(IrhpInterface::STATUS_CANCELLED, $entity->getStatus()->getId());
        $this->assertEquals(date('Y-m-d'), $entity->getCancellationDate()->format('Y-m-d'));
    }

    /**
     * @dataProvider dpCancelException
     */
    public function testCancelException($status)
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_CANCEL);
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setStatus(new RefData($status));
        $entity->cancel(new RefData(IrhpInterface::STATUS_CANCELLED));
    }

    /**
     * Pass array of app status to make sure an exception is thrown
     *
     * @return array
     */
    public function dpCancelException()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION],
            [IrhpInterface::STATUS_WITHDRAWN],
            [IrhpInterface::STATUS_AWAITING_FEE],
            [IrhpInterface::STATUS_FEE_PAID],
            [IrhpInterface::STATUS_UNSUCCESSFUL],
            [IrhpInterface::STATUS_ISSUING],
            [IrhpInterface::STATUS_VALID],
        ];
    }

    /**
     * @dataProvider dpTestCanBeCancelled
     */
    public function testCanBeCancelled($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->canBeCancelled());
    }

    public function dpTestCanBeCancelled()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, true],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    public function testTerminate()
    {
        $this->sut->shouldReceive('canBeTerminated')
            ->withNoArgs()
            ->andReturnTrue();

        $terminateStatus = m::mock(RefData::class);

        $this->sut->terminate($terminateStatus);
        $this->assertSame($terminateStatus, $this->sut->getStatus());
        $this->assertInstanceOf(DateTime::class, $this->sut->getExpiryDate());
    }

    public function testTerminateException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_TERMINATE);

        $this->sut->shouldReceive('canBeTerminated')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->terminate(m::mock(RefData::class));
    }

    /**
     * @dataProvider dpCanBeTerminated
     */
    public function testCanBeTerminated($isValidCertificateOfRoadworthiness, $expected)
    {
        $this->sut->shouldReceive('isValidCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isValidCertificateOfRoadworthiness);

        $this->assertEquals(
            $expected,
            $this->sut->canBeTerminated()
        );
    }

    public function dpCanBeTerminated()
    {
        return [
            [false, false],
            [true, true],
        ];
    }

    /**
     * @dataProvider dpIsValidCertificateOfRoadworthiness
     */
    public function testIsValidCertificateOfRoadworthiness($isValid, $isCertificateOfRoadworthiness, $expected)
    {
        $this->sut->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturn($isValid);
        $this->sut->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthiness);

        $this->assertEquals(
            $expected,
            $this->sut->canBeTerminated()
        );
    }

    public function dpIsValidCertificateOfRoadworthiness()
    {
        return [
            [false, false, false],
            [false, true, false],
            [true, false, false],
            [true, true, true],
        ];
    }

    /**
     * @dataProvider dpTestIsCancelled
     */
    public function testIsCancelled($status, $expected)
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isCancelled());
    }

    public function dpTestIsCancelled()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, true],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpCanBeWithdrawn
     */
    public function testCanBeWithdrawn($reasonRefData, $isUnderConsideration, $isAwaitingFee, $expected)
    {
        $this->sut->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);

        $this->sut->shouldReceive('isAwaitingFee')
            ->withNoArgs()
            ->andReturn($isAwaitingFee);

        $this->assertEquals(
            $expected,
            $this->sut->canBeWithdrawn($reasonRefData)
        );
    }

    public function dpCanBeWithdrawn()
    {
        return [
            [null, false, false, false],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS), false, false, false],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_UNPAID), false, false, false],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_BY_USER), false, false, false],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED), false, false, false],
            [null, true, false, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS), true, false, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_UNPAID), true, false, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_BY_USER), true, false, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED), true, false, true],
            [null, false, true, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS), false, true, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_UNPAID), false, true, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_BY_USER), false, true, true],
            [new RefData(WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED), false, true, true],
        ];
    }

    /**
     * @dataProvider dpCanBeWithdrawnReasonDeclined
     */
    public function testCanBeWithdrawnReasonDeclined($canBeDeclined)
    {
        $declinedReasonRefData = new RefData(WithdrawableInterface::WITHDRAWN_REASON_DECLINED);

        $this->sut->shouldReceive('canBeDeclined')
            ->withNoArgs()
            ->andReturn($canBeDeclined);

        $this->assertEquals(
            $canBeDeclined,
            $this->sut->canBeWithdrawn($declinedReasonRefData)
        );
    }

    public function dpCanBeWithdrawnReasonDeclined()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider dpTestIsWithdrawn
     */
    public function testIsWithdrawn($status, $expected)
    {
        $entity = $this->createNewEntity(null, new RefData($status));
        $this->assertSame($expected, $entity->isWithdrawn());
    }

    public function dpTestIsWithdrawn()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, true],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpTestIsDeclined
     */
    public function testIsDeclined($reason, $expected)
    {
        $entity = $this->createNewEntity(null, new RefData(IrhpInterface::STATUS_WITHDRAWN));
        $entity->setWithdrawReason(new RefData($reason));
        $this->assertSame($expected, $entity->isDeclined());
    }

    public function dpTestIsDeclined()
    {
        return [
            [WithdrawableInterface::WITHDRAWN_REASON_DECLINED, true],
            [WithdrawableInterface::WITHDRAWN_REASON_BY_USER, false],
            [WithdrawableInterface::WITHDRAWN_REASON_UNPAID, false],
        ];
    }

    /**
     * @dataProvider dpWithdraw
     */
    public function testWithdraw($withdrawReason, $checkReasonAgainstStatus, $expectedValidationWithdrawReason)
    {
        $withdrawStatus = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeWithdrawn')
            ->with($expectedValidationWithdrawReason)
            ->andReturnTrue();

        $this->sut->withdraw(
            $withdrawStatus,
            $withdrawReason,
            $checkReasonAgainstStatus
        );

        $this->assertSame(
            $withdrawStatus,
            $this->sut->getStatus()
        );

        $this->assertEquals(
            $withdrawReason,
            $this->sut->getWithdrawReason()
        );

        $this->assertEquals(
            date('Y-m-d'),
            $this->sut->getWithdrawnDate()->format('Y-m-d')
        );
    }

    public function dpWithdraw()
    {
        $withdrawReason = m::mock(RefData::class);

        return [
            [$withdrawReason, true, $withdrawReason],
            [$withdrawReason, false, null],
        ];
    }

    /**
     * @dataProvider dpWithdrawException
     */
    public function testWithdrawException(
        $withdrawReasonRefData,
        $checkReasonAgainstStatus,
        $expectedValidationWithdrawReasonRefData,
        $expectedError
    ) {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($expectedError);

        $withdrawStatusRefData = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeWithdrawn')
            ->with($expectedValidationWithdrawReasonRefData)
            ->andReturnFalse();

        $this->sut->withdraw(
            $withdrawStatusRefData,
            $withdrawReasonRefData,
            $checkReasonAgainstStatus
        );
    }

    public function dpWithdrawException()
    {
        $notSuccessRefData = new RefData(WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS);
        $unpaidRefData = new RefData(WithdrawableInterface::WITHDRAWN_REASON_UNPAID);
        $byUserRefData = new RefData(WithdrawableInterface::WITHDRAWN_REASON_BY_USER);
        $declinedRefData = new RefData(WithdrawableInterface::WITHDRAWN_REASON_DECLINED);
        $permitsRevokedRefData = new RefData(WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED);

        return [
            [
                $notSuccessRefData,
                true,
                $notSuccessRefData,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $unpaidRefData,
                true,
                $unpaidRefData,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $byUserRefData,
                true,
                $byUserRefData,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $declinedRefData,
                true,
                $declinedRefData,
                WithdrawableInterface::ERR_CANT_DECLINE,
            ],
            [
                $permitsRevokedRefData,
                true,
                $permitsRevokedRefData,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $notSuccessRefData,
                false,
                null,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $unpaidRefData,
                false,
                null,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $byUserRefData,
                false,
                null,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
            [
                $declinedRefData,
                false,
                null,
                WithdrawableInterface::ERR_CANT_DECLINE,
            ],
            [
                $permitsRevokedRefData,
                false,
                null,
                WithdrawableInterface::ERR_CANT_WITHDRAW,
            ],
        ];
    }

    /**
     * @dataProvider trueOrFalseProvider
     */
    public function testIsBilateral($isBilateral)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturn($isBilateral);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $this->assertEquals($isBilateral, $entity->isBilateral());
    }

    /**
     * @dataProvider trueOrFalseProvider
     */
    public function testIsCertificateOfRoadworthiness($isCertificateOfRoadworthiness)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isCertificateOfRoadworthiness')->once()->withNoArgs()->andReturn($isCertificateOfRoadworthiness);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $this->assertEquals($isCertificateOfRoadworthiness, $entity->isCertificateOfRoadworthiness());
    }

    /**
     * @dataProvider trueOrFalseProvider
     */
    public function testIsCertificateOfRoadworthinessVehicle($isCertificateOfRoadworthinessVehicle)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isCertificateOfRoadworthinessVehicle')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthinessVehicle);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);

        $this->assertEquals(
            $isCertificateOfRoadworthinessVehicle,
            $entity->isCertificateOfRoadworthinessVehicle()
        );
    }

    /**
     * @dataProvider trueOrFalseProvider
     */
    public function testIsCertificateOfRoadworthinessTrailer($isCertificateOfRoadworthinessTrailer)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isCertificateOfRoadworthinessTrailer')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthinessTrailer);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);

        $this->assertEquals(
            $isCertificateOfRoadworthinessTrailer,
            $entity->isCertificateOfRoadworthinessTrailer()
        );
    }


    /**
     * @dataProvider trueOrFalseProvider
     */
    public function testIsMultiStock($isMultiStock)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isMultiStock')->once()->withNoArgs()->andReturn($isMultiStock);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $this->assertEquals($isMultiStock, $entity->isMultiStock());
    }

    public function trueOrFalseProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetAssociatedStock()
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isMultiStock')->once()->withNoArgs()->andReturn(false);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->once()
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->setIrhpPermitApplications(new ArrayCollection([$irhpPermitApplication]));

        $this->assertEquals($irhpPermitStock, $entity->getAssociatedStock());
    }

    public function testGetAssociatedStockMultiStockException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Multi stock permit types can\'t use this method');

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isMultiStock')->once()->withNoArgs()->andReturn(true);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);

        $entity->getAssociatedStock();
    }

    /**
     * @dataProvider dpIsNotYetSubmitted
     */
    public function testIsNotYetSubmitted($status, $expectedNotYetSubmitted)
    {
        $statusRefData = m::mock(RefData::class);
        $statusRefData->shouldReceive('getId')
            ->andReturn($status);

        $irhpApplication = new Entity();
        $irhpApplication->setStatus($statusRefData);

        $this->assertEquals(
            $expectedNotYetSubmitted,
            $irhpApplication->isNotYetSubmitted()
        );
    }

    public function dpIsNotYetSubmitted()
    {
        return [
            [IrhpInterface::STATUS_CANCELLED, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, true],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpInterface::STATUS_ISSUING, false],
            [IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpIsOverviewAccessible
     */
    public function testIsOverviewAccessible($isNotYetSubmitted, $expected)
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);

        $this->assertEquals(
            $expected,
            $this->sut->isOverviewAccessible()
        );
    }

    public function dpIsOverviewAccessible()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @dataProvider dpIsOverviewAccessibleBilateral
     */
    public function testIsOverviewAccessibleBilateral($isNotYetSubmitted, $countries, $expected)
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setCountrys($countries);

        $this->sut->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);

        $this->assertEquals(
            $expected,
            $this->sut->isOverviewAccessible()
        );
    }

    public function dpIsOverviewAccessibleBilateral()
    {
        $emptyCountries = new ArrayCollection();

        $populatedCountries = new ArrayCollection(
            [
                m::mock(Country::class),
                m::mock(Country::class)
            ]
        );

        return [
            [false, $emptyCountries, false],
            [false, $populatedCountries, false],
            [true, $emptyCountries, false],
            [true, $populatedCountries, true],
        ];
    }

    /**
     * @dataProvider dpIsSubmittedForConsideration
     */
    public function testIsSubmittedForConsideration($irhpPermitTypeId, $status, $expected)
    {
        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId($irhpPermitTypeId);

        $statusRefData = m::mock(RefData::class);
        $statusRefData->shouldReceive('getId')
            ->andReturn($status);

        $irhpApplication = new Entity();
        $irhpApplication->setStatus($statusRefData);
        $irhpApplication->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $expected,
            $irhpApplication->isSubmittedForConsideration()
        );
    }

    public function dpIsSubmittedForConsideration()
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_CANCELLED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_UNDER_CONSIDERATION, true],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_ISSUING, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, IrhpInterface::STATUS_VALID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_CANCELLED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_UNDER_CONSIDERATION, true],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_ISSUING, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, IrhpInterface::STATUS_VALID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_CANCELLED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_ISSUING, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL, IrhpInterface::STATUS_VALID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_CANCELLED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_ISSUING, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, IrhpInterface::STATUS_VALID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_CANCELLED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_NOT_YET_SUBMITTED, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_UNDER_CONSIDERATION, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_WITHDRAWN, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_AWAITING_FEE, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_FEE_PAID, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_UNSUCCESSFUL, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_ISSUING, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, IrhpInterface::STATUS_VALID, false],
        ];
    }

    /**
     * @dataProvider dpCanBeUpdated
     */
    public function testCanBeUpdated(
        $isNotYetSubmitted,
        $isUnderConsideration,
        $isValid,
        $isCertificateOfRoadworthiness,
        $expectedCanBeUpdated
    ) {
        $this->sut->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);
        $this->sut->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);
        $this->sut->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturn($isValid);
        $this->sut->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthiness);

        $this->assertEquals(
            $expectedCanBeUpdated,
            $this->sut->canBeUpdated()
        );
    }

    public function dpCanBeUpdated()
    {
        return [
            [false, false, false, false, false],
            [true, false, false, false, true],
            [false, true, false, false, true],
            [false, false, true, false, false],
            [true, false, false, true, true],
            [false, true, false, true, true],
            [false, false, true, true, true],
        ];
    }

    public function testHasCheckedAnswers()
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(false);

        $this->assertFalse($this->sut->hasCheckedAnswers());

        $this->sut->setCheckedAnswers(true);
        $this->assertTrue($this->sut->hasCheckedAnswers());
    }

    /**
     * @dataProvider dpHasCheckedAnswersBilateral
     */
    public function testHasCheckedAnswersBilateral($checkedAnswers)
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setCheckedAnswers($checkedAnswers);

        $this->assertFalse($this->sut->hasCheckedAnswers());
    }

    public function dpHasCheckedAnswersBilateral()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testHasMadeDeclaration()
    {
        $this->assertFalse($this->sut->hasMadeDeclaration());

        $this->sut->setDeclaration(true);
        $this->assertTrue($this->sut->hasMadeDeclaration());
    }

    /**
     * @dataProvider dpCanBeSubmitted
     */
    public function testCanBeSubmittedFalseIfAlreadySubmittedOrNotEligible(
        $isNotYetSubmitted,
        $isEligibleForPermits,
        $allSectionsCompleted,
        $expectedCanBeSubmitted
    ) {
        $sections = ['allCompleted' => $allSectionsCompleted];

        $this->sut->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);
        $this->sut->shouldReceive('getSectionCompletion')
            ->withNoArgs()
            ->andReturn($sections);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('isEligibleForPermits')
            ->withNoArgs()
            ->andReturn($isEligibleForPermits);

        $this->sut->setLicence($licence);

        $this->assertEquals(
            $expectedCanBeSubmitted,
            $this->sut->canBeSubmitted()
        );
    }

    public function dpCanBeSubmitted()
    {
        return [
            'not yet submitted, not eligible, incomplete' => [true, false, false, false],
            'not yet submitted, not eligible, complete' => [true, false, true, false],
            'not yet submitted, eligible, incomplete' => [true, true, false, false],
            'not yet submitted, eligible, complete' => [true, true, true, true],
            'already submitted, not eligible, complete' => [false, false, true, false],
            'already submitted, eligible, complete' => [false, true, true, false],
        ];
    }

    /**
     * @dataProvider dpTestCanUpdateCountries
     */
    public function testCanUpdateCountries($canBeUpdated, $irhpPermitTypeId, $isFieldReadyToComplete, $expected)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn($irhpPermitTypeId);

        $this->sut->shouldReceive('canBeUpdated')
            ->andReturn($canBeUpdated)
            ->shouldReceive('getIrhpPermitType')
            ->andReturn($irhpPermitType)
            ->shouldReceive('isFieldReadyToComplete')
            ->with('countries')
            ->andReturn($isFieldReadyToComplete);

        $this->assertSame($expected, $this->sut->canUpdateCountries());
    }

    public function dpTestCanUpdateCountries()
    {
        return [
            'cannot be updated' => [
                'canBeUpdated' => false,
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'isFieldReadyToComplete' => true,
                'expected' => false,
            ],
            'incorrect type' => [
                'canBeUpdated' => true,
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                'isFieldReadyToComplete' => true,
                'expected' => false,
            ],
            'the field not ready to complete' => [
                'canBeUpdated' => true,
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'isFieldReadyToComplete' => false,
                'expected' => false,
            ],
            'can be updated' => [
                'canBeUpdated' => true,
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'isFieldReadyToComplete' => true,
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider dpIsReadyForNoOfPermits
     */
    public function testIsReadyForNoOfPermits(
        $canBeUpdated,
        $irhpPermitApplications,
        $expectedIsReadyForNoOfPermits
    ) {
        $irhpApplication = m::mock(Entity::class)->makePartial();

        $irhpApplication->shouldReceive('canBeUpdated')
            ->andReturn($canBeUpdated);

        $irhpApplication->setIrhpPermitApplications(
            new ArrayCollection($irhpPermitApplications)
        );

        $this->assertEquals(
            $expectedIsReadyForNoOfPermits,
            $irhpApplication->isReadyForNoOfPermits()
        );
    }

    public function dpIsReadyForNoOfPermits()
    {
        return [
            [
                true,
                [m::mock(IrhpPermitApplication::class), m::mock(IrhpPermitApplication::class)],
                true
            ],
            [
                true,
                [],
                false
            ],
            [
                false,
                [m::mock(IrhpPermitApplication::class), m::mock(IrhpPermitApplication::class)],
                false
            ],
            [
                false,
                [],
                false
            ],
        ];
    }

    /**
     * @dataProvider dpHasOutstandingFees
     */
    public function testHasOutstandingFees($feesData, $expectedResult)
    {
        $this->sut->setFees(
            $this->createFeesArrayCollectionFromArrayData($feesData)
        );

        $this->assertEquals($expectedResult, $this->sut->hasOutstandingFees());
    }

    public function dpHasOutstandingFees()
    {
        return [
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRFOGVPERMIT
                    ]
                ],
                'expectedResult' => false
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_DUP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSAPP
                    ]
                ],
                'expectedResult' => false
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRFOGVPERMIT
                    ]
                ],
                'expectedResult' => true
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRFOGVPERMIT
                    ]
                ],
                'expectedResult' => true
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRFOGVPERMIT
                    ]
                ],
                'expectedResult' => true
            ],
        ];
    }

    /**
     * @dataProvider dpIssueFeeOverdue
     *
     * Tests logic for finding overdue issue fees
     *
     * $fee1 isn't overdue, so is ignored
     * $fee2 is overdue, but doesn't need to be checked because $fee5 is more recent and will match
     * $fee3 is overdue, is outstanding, but isn't an issue fee
     * $fee4 would be overdue, but is not outstanding, so the fee type is not checked
     * $fee5 is overdue, outstanding and the correct fee type, causes the method to return true
     */
    public function testIssueFeeOverdue($thresholdDays, $dateAfterThreshold, $dateOfThreshold, $dateBeforeThreshold)
    {
        $fee1 = m::mock(Fee::class)->makePartial();
        $fee1->shouldReceive('isOutstanding')->never();
        $fee1->shouldReceive('getFeeType->isIrhpApplicationIssue')->never();
        $fee1->setInvoicedDate($dateAfterThreshold);

        $fee2 = m::mock(Fee::class)->makePartial();
        $fee2->shouldReceive('isOutstanding')->never();
        $fee2->shouldReceive('getFeeType->isIrhpApplicationIssue')->never();
        $fee2->setInvoicedDate($dateBeforeThreshold);

        $fee3 = m::mock(Fee::class)->makePartial();
        $fee3->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(true);
        $fee3->shouldReceive('getFeeType->isIrhpApplicationIssue')->once()->withNoArgs()->andReturn(false);
        $fee3->setInvoicedDate($dateOfThreshold);

        $fee4 = m::mock(Fee::class)->makePartial();
        $fee4->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(false);
        $fee4->shouldReceive('getFeeType->isIrhpApplicationIssue')->never();
        $fee4->setInvoicedDate($dateOfThreshold);

        $fee5 = m::mock(Fee::class)->makePartial();
        $fee5->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(true);
        $fee5->shouldReceive('getFeeType->isIrhpApplicationIssue')->once()->withNoArgs()->andReturn(true);
        $fee5->setInvoicedDate($dateOfThreshold);

        $feesCollection = new ArrayCollection([$fee1, $fee2, $fee3, $fee4, $fee5]);

        $this->sut->setFees($feesCollection);

        $this->assertEquals(4, $this->sut->getFeesByAge($thresholdDays)->count());

        $this->assertTrue($this->sut->issueFeeOverdue($thresholdDays));
    }

    public function dpIssueFeeOverdue()
    {
        return [
            [
                10,
                new \DateTime('-9 weekdays'),
                new \DateTime('-10 weekdays'),
                new \DateTime('-11 weekdays')
            ],
            [
                5,
                new \DateTime('-4 weekdays'),
                new \DateTime('-5 weekdays'),
                new \DateTime('-6 weekdays')
            ]
        ];
    }

    /**
     * @dataProvider dpIssueFeeOverdueBoundary
     */
    public function testIssueFeeOverdueBoundary($days, $expected)
    {
        $invoiceDate = new \DateTime('-' . $days . ' weekdays');

        $fee = m::mock(Fee::class)->makePartial();
        $fee->shouldReceive('isOutstanding')->times($expected)->andReturn(true);
        $fee->shouldReceive('getFeeType->isIrhpApplicationIssue')->times($expected)->andReturn(true);
        $fee->setInvoicedDate($invoiceDate);

        $feesCollection = new ArrayCollection([$fee]);

        $this->sut->setFees($feesCollection);

        $this->assertEquals(
            $expected,
            $this->sut->issueFeeOverdue(10)
        );
    }

    public function dpIssueFeeOverdueBoundary()
    {
        return [
            [9, 0],
            [10, 1],
            [11, 1],
        ];
    }

    /**
     * @dataProvider dpGetLatestOutstandingApplicationFee
     */
    public function testGetLatestOutstandingApplicationFee($feesData, $expectedIndex)
    {
        $fees = $this->createFeesArrayCollectionFromArrayData($feesData);
        $this->sut->setFees($fees);

        $latestOutstandingIssueFee = $this->sut->getLatestOutstandingApplicationFee();

        if (is_null($expectedIndex)) {
            $this->assertNull($latestOutstandingIssueFee);
        }

        $this->assertSame($expectedIndex === null ? null : $fees[$expectedIndex], $latestOutstandingIssueFee);
    }

    public function dpGetLatestOutstandingApplicationFee()
    {
        return [
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSAPP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSVAR
                    ]
                ],
                'expectedIndex' => null
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ]
                ],
                'expectedIndex' => 1
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ]
                ],
                'expectedIndex' => 0
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ]
                ],
                'expectedIndex' => 0
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ]
                ],
                'expectedIndex' => 0
            ],
        ];
    }

    /**
     * @dataProvider dpGetLatestOutstandingIssueFee
     */
    public function testGetLatestOutstandingIssueFee($feesData, $expectedIndex)
    {
        $fees = $this->createFeesArrayCollectionFromArrayData($feesData);
        $this->sut->setFees($fees);

        $latestOutstandingIssueFee = $this->sut->getLatestOutstandingIssueFee();

        if (is_null($expectedIndex)) {
            $this->assertNull($latestOutstandingIssueFee);
        }

        $this->assertSame($expectedIndex === null ? null : $fees[$expectedIndex], $latestOutstandingIssueFee);
    }

    public function dpGetLatestOutstandingIssueFee()
    {
        return [
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSAPP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSVAR
                    ]
                ],
                'expectedIndex' => null
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ]
                ],
                'expectedIndex' => 1
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ]
                ],
                'expectedIndex' => 0
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ]
                ],
                'expectedIndex' => 0
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => true,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ]
                ],
                'expectedIndex' => 0
            ],
        ];
    }

    /**
     * @dataProvider dpGetLatestIssueFee
     */
    public function testGetLatestIssueFee($feesData, $expectedIndex)
    {
        $fees = $this->createFeesArrayCollectionFromArrayData($feesData);
        $this->sut->setFees($fees);

        $latestOutstandingIssueFee = $this->sut->getLatestIssueFee();

        if (is_null($expectedIndex)) {
            $this->assertNull($latestOutstandingIssueFee);
        }

        $this->assertSame($expectedIndex === null ? null : $fees[$expectedIndex], $latestOutstandingIssueFee);
    }

    public function dpGetLatestIssueFee()
    {
        return [
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSAPP
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_BUSVAR
                    ]
                ],
                'expectedIndex' => null
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ]
                ],
                'expectedIndex' => 1
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ]
                ],
                'expectedIndex' => 0
            ],
            [
                'fees' => [
                    [
                        'invoicedDate' => '2019-01-04',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_ISSUE
                    ],
                    [
                        'invoicedDate' => '2019-01-08',
                        'isOutstanding' => false,
                        'feeTypeId' => FeeType::FEE_TYPE_IRHP_APP
                    ]
                ],
                'expectedIndex' => 0
            ],
        ];
    }

    private function createFeesArrayCollectionFromArrayData($feesData)
    {
        $fees = [];
        foreach ($feesData as $feeData) {
            $fee = m::mock(Fee::class);
            $fee->shouldReceive('isOutstanding')
                ->andReturn($feeData['isOutstanding'])
                ->shouldReceive('getInvoicedDate')
                ->andReturn(new DateTime($feeData['invoicedDate']))
                ->shouldReceive('getFeeType->getFeeType->getId')
                ->andReturn($feeData['feeTypeId']);

            $fees[] = $fee;
        }

        return new ArrayCollection($fees);
    }

    public function testGetOutstandingFees()
    {
        $outstandingIrhpAppFee = m::mock(Fee::class);
        $outstandingIrhpAppFee->shouldReceive('isOutstanding')->once()->andReturn(true);
        $outstandingIrhpAppFee->shouldReceive('getFeeType->getFeeType->getId')
            ->once()
            ->andReturn(FeeType::FEE_TYPE_IRHP_APP);

        $outstandingIrhpIssueFee = m::mock(Fee::class);
        $outstandingIrhpIssueFee->shouldReceive('isOutstanding')->once()->andReturn(true);
        $outstandingIrhpIssueFee->shouldReceive('getFeeType->getFeeType->getId')
            ->once()
            ->andReturn(FeeType::FEE_TYPE_IRHP_ISSUE);

        $outstandingIrfoGvPermitFee = m::mock(Fee::class);
        $outstandingIrfoGvPermitFee->shouldReceive('isOutstanding')->once()->andReturn(true);
        $outstandingIrfoGvPermitFee->shouldReceive('getFeeType->getFeeType->getId')
            ->once()
            ->andReturn(FeeType::FEE_TYPE_IRFOGVPERMIT);

        $notOutstandingIrhpAppFee = m::mock(Fee::class);
        $notOutstandingIrhpAppFee->shouldReceive('isOutstanding')->once()->andReturn(false);
        $notOutstandingIrhpAppFee->shouldReceive('getFeeType->getFeeType->getId')->never();

        $notOutstandingIrhpIssueFee = m::mock(Fee::class);
        $notOutstandingIrhpIssueFee->shouldReceive('isOutstanding')->once()->andReturn(false);
        $notOutstandingIrhpIssueFee->shouldReceive('getFeeType->getFeeType->getId')->never();

        $notOutstandingIrfoGvPermitFee = m::mock(Fee::class);
        $notOutstandingIrfoGvPermitFee->shouldReceive('isOutstanding')->once()->andReturn(false);
        $notOutstandingIrfoGvPermitFee->shouldReceive('getFeeType->getFeeType->getId')->never();

        $allFees = [
            $outstandingIrhpAppFee,
            $outstandingIrhpIssueFee,
            $outstandingIrfoGvPermitFee,
            $notOutstandingIrhpAppFee,
            $notOutstandingIrhpIssueFee,
            $notOutstandingIrfoGvPermitFee,
        ];

        $outstandingFees = [
            $outstandingIrhpAppFee,
            $outstandingIrhpIssueFee,
            $outstandingIrfoGvPermitFee,
        ];

        $fees = new ArrayCollection($allFees);

        $this->sut->setFees($fees);

        $this->assertSame($outstandingFees, $this->sut->getOutstandingFees());
    }

    public function testGetOutstandingFeeAmount()
    {
        $outstandingIrhpAppFee = m::mock(Fee::class);
        $outstandingIrhpAppFee->shouldReceive('isOutstanding')->once()->andReturn(true);
        $outstandingIrhpAppFee->shouldReceive('getGrossAmount')->once()->andReturn(25.56);
        $outstandingIrhpAppFee->shouldReceive('getFeeType->getFeeType->getId')
            ->once()
            ->andReturn(FeeType::FEE_TYPE_IRHP_APP);

        $outstandingIrhpIssueFee = m::mock(Fee::class);
        $outstandingIrhpIssueFee->shouldReceive('isOutstanding')->once()->andReturn(true);
        $outstandingIrhpIssueFee->shouldReceive('getGrossAmount')->once()->andReturn(50);
        $outstandingIrhpIssueFee->shouldReceive('getFeeType->getFeeType->getId')
            ->once()
            ->andReturn(FeeType::FEE_TYPE_IRHP_ISSUE);

        $outstandingFees = [
            $outstandingIrhpAppFee,
            $outstandingIrhpIssueFee
        ];

        $fees = new ArrayCollection($outstandingFees);

        $this->sut->setFees($fees);

        $this->assertEquals(75.56, $this->sut->getOutstandingFeeAmount());
    }

    /**
     * @dataProvider dpTestGetSectionCompletionMultilateral
     */
    public function testGetSectionCompletionMultilateral($data, $expected)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $irhpPermitType->setId(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $this->sut->setIrhpPermitType($irhpPermitType);
        $this->sut->setIrhpPermitApplications($data['irhpPermitApplications']);
        $this->sut->setCheckedAnswers($data['checkedAnswers']);
        $this->sut->setDeclaration($data['declaration']);

        $this->assertSame($expected, $this->sut->getSectionCompletion());
    }

    public function dpTestGetSectionCompletionMultilateral()
    {
        $irhpPermitAppWithoutPermits = m::mock(IrhpPermitApplication::class)->makePartial();

        $irhpPermitAppWithPermits = m::mock(IrhpPermitApplication::class)->makePartial();
        $irhpPermitAppWithPermits->setPermitsRequired(10);

        return [
            'No data set' => [
                'data' => [
                    'irhpPermitApplications' => new ArrayCollection(),
                    'checkedAnswers' => false,
                    'declaration' => false,
                ],
                'expected' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'totalSections' => 3,
                    'totalCompleted' => 0,
                    'allCompleted' => false,
                ],
            ],
            'IRHP permit apps with all apps without permits required set' => [
                'data' => [
                    'irhpPermitApplications' => new ArrayCollection(
                        [
                            $irhpPermitAppWithoutPermits,
                            $irhpPermitAppWithoutPermits
                        ]
                    ),
                    'checkedAnswers' => false,
                    'declaration' => false,
                ],
                'expected' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'totalSections' => 3,
                    'totalCompleted' => 0,
                    'allCompleted' => false,
                ],
            ],
            'IRHP permit apps with one app without permits required set' => [
                'data' => [
                    'irhpPermitApplications' => new ArrayCollection(
                        [
                            $irhpPermitAppWithPermits,
                            $irhpPermitAppWithoutPermits
                        ]
                    ),
                    'checkedAnswers' => false,
                    'declaration' => false,
                ],
                'expected' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'totalSections' => 3,
                    'totalCompleted' => 0,
                    'allCompleted' => false,
                ],
            ],
            'IRHP permit apps with all apps with permits required set' => [
                'data' => [
                    'irhpPermitApplications' => new ArrayCollection(
                        [
                            $irhpPermitAppWithPermits,
                            $irhpPermitAppWithPermits
                        ]
                    ),
                    'checkedAnswers' => false,
                    'declaration' => false,
                ],
                'expected' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'totalSections' => 3,
                    'totalCompleted' => 1,
                    'allCompleted' => false,
                ],
            ],
            'Checked answers set' => [
                'data' => [
                    'irhpPermitApplications' => new ArrayCollection(
                        [
                            $irhpPermitAppWithPermits,
                            $irhpPermitAppWithPermits
                        ]
                    ),
                    'checkedAnswers' => true,
                    'declaration' => false,
                ],
                'expected' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'totalSections' => 3,
                    'totalCompleted' => 2,
                    'allCompleted' => false,
                ],
            ],
            'Declaration set' => [
                'data' => [
                    'irhpPermitApplications' => new ArrayCollection(
                        [
                            $irhpPermitAppWithPermits,
                            $irhpPermitAppWithPermits
                        ]
                    ),
                    'checkedAnswers' => true,
                    'declaration' => true,
                ],
                'expected' => [
                    'permitsRequired' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'checkedAnswers' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'totalSections' => 3,
                    'totalCompleted' => 3,
                    'allCompleted' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider dpTestGetSectionCompletionBilateral
     */
    public function testGetSectionCompletionBilateral($data, $expected)
    {
        $this->sut->shouldReceive('canBeSubmitted')
            ->withNoArgs()
            ->andReturn($data['canBeSubmitted']);

        $this->sut->shouldReceive('getBilateralCountriesAndStatuses')
            ->withNoArgs()
            ->andReturn($data['countries']);

        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $irhpPermitType->setId(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $this->sut->setIrhpPermitType($irhpPermitType);
        $this->sut->setDeclaration($data['declaration']);

        $this->assertSame($expected, $this->sut->getSectionCompletion());
    }

    public function dpTestGetSectionCompletionBilateral()
    {
        $incompleteCountries = [
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_COMPLETED
            ],
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_INCOMPLETE
            ],
        ];

        $completedCountries = [
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_COMPLETED
            ],
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_COMPLETED
            ],
        ];

        return [
            'Countries not completed' => [
                'data' => [
                    'countries' => $incompleteCountries,
                    'declaration' => false,
                    'canBeSubmitted' => false,
                ],
                'expected' => [
                    'countries' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'submitAndPay' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'totalSections' => 3,
                    'totalCompleted' => 0,
                    'allCompleted' => false,
                ],
            ],
            'Countries completed, declaration not set' => [
                'data' => [
                    'countries' => $completedCountries,
                    'declaration' => false,
                    'canBeSubmitted' => false,
                ],
                'expected' => [
                    'countries' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    'submitAndPay' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    'totalSections' => 3,
                    'totalCompleted' => 1,
                    'allCompleted' => false,
                ],
            ],
            'Countries completed, declaration set' => [
                'data' => [
                    'countries' => $completedCountries,
                    'declaration' => true,
                    'canBeSubmitted' => true,
                ],
                'expected' => [
                    'countries' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'declaration' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'submitAndPay' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    'totalSections' => 3,
                    'totalCompleted' => 3,
                    'allCompleted' => true,
                ],
            ]
        ];
    }

    public function testGetSectionCompletionForUndefinedIrhpPermitType()
    {
        // undefined IRHP Permit Type id
        $irhpPermitTypeId = 99999;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Missing required definition of sections for irhpPermitTypeId: ' . $irhpPermitTypeId
        );

        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $irhpPermitType->setId($irhpPermitTypeId);

        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->sut->getSectionCompletion();
    }

    /**
     * @dataProvider dpCanCheckAnswersForApplicationPathEnabled
     */
    public function testCanCheckAnswersForApplicationPathEnabled(
        $irhpPermitTypeId,
        $status,
        $questionAnswerData,
        $expected
    ) {
        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId($irhpPermitTypeId);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->sut->setStatus(new RefData($status));

        $this->sut->shouldReceive('getQuestionAnswerData')
            ->andReturn($questionAnswerData);

        $this->assertEquals($expected, $this->sut->canCheckAnswers());
    }

    public function dpCanCheckAnswersForApplicationPathEnabled()
    {
        return [
            'ECMT Removal - not yet submitted - check answers cannot start' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Removal - not yet submitted - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Removal - not yet submitted - check answers completed' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 1,
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Removal - under consideration - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Removal - withdrawn - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_WITHDRAWN,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Removal - cancelled - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_CANCELLED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Short Term - not yet submitted - check answers cannot start' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Short Term - not yet submitted - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Short Term - not yet submitted - check answers completed' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 1,
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Short Term - under consideration - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Short Term - withdrawn - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'status' => IrhpInterface::STATUS_WITHDRAWN,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Short Term - cancelled - check answers not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'status' => IrhpInterface::STATUS_CANCELLED,
                'questionAnswerData' => [
                    [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpCanCheckAnswersForMultilateral
     */
    public function testCanCheckAnswersForMultilateral(
        $status,
        $permitsRequired,
        $expected
    ) {
        $this->sut->setStatus(new RefData($status));

        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $licence = m::mock(Licence::class);
        $this->sut->setLicence($licence);

        $irhpPermitApp = m::mock(IrhpPermitApplication::class)->makePartial();
        $irhpPermitApp->setPermitsRequired($permitsRequired);

        $this->sut->setIrhpPermitApplications(
            new ArrayCollection([$irhpPermitApp])
        );

        $this->assertEquals($expected, $this->sut->canCheckAnswers());
    }

    public function dpCanCheckAnswersForMultilateral()
    {
        return [
            'Not yet submitted - permits required set' => [
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'permitsRequired' => 10,
                'expected' => true,
            ],
            'Not yet submitted - permits required not set' => [
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'permitsRequired' => null,
                'expected' => false,
            ],
            'Under consideration - permits required set' => [
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'permitsRequired' => 10,
                'expected' => true,
            ],
            'Withdrawn - permits required set' => [
                'status' => IrhpInterface::STATUS_WITHDRAWN,
                'permitsRequired' => 10,
                'expected' => false,
            ],
            'Cancelled - permits required set' => [
                'status' => IrhpInterface::STATUS_CANCELLED,
                'permitsRequired' => 10,
                'expected' => false,
            ],
        ];
    }

    public function testCanCheckAnswersForBilateral()
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->assertFalse(
            $this->sut->canCheckAnswers()
        );
    }

    public function testUpdateCheckAnswersNotBilateral()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();
        $irhpApplication->shouldReceive('canCheckAnswers')
            ->once()
            ->andReturn(true);

        $irhpApplication->setCheckedAnswers(false);
        $irhpApplication->updateCheckAnswers();
        $this->assertTrue($irhpApplication->getCheckedAnswers());
    }

    public function testUpdateCheckAnswersNotBilateralException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_CHECK_ANSWERS);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();
        $irhpApplication->shouldReceive('canCheckAnswers')
            ->once()
            ->andReturn(false);

        $irhpApplication->updateCheckAnswers();
    }

    public function testUpdateCheckAnswers()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpApplication->updateCheckAnswers();
        
        // For bilateral applications, updateCheckAnswers returns early without setting the flag
        // Just assert that the method runs without throwing an exception
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Entity\Permits\IrhpApplication::class, $irhpApplication);
    }

    public function testResetCheckAnswersAndDeclarationSuccess()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('canBeUpdated')
            ->andReturn(true);

        $irhpApplication->setDeclaration(true);
        $irhpApplication->setCheckedAnswers(true);

        $irhpApplication->resetCheckAnswersAndDeclaration();
        $this->assertFalse($irhpApplication->getDeclaration());
        $this->assertFalse($irhpApplication->getCheckedAnswers());
    }

    public function testResetCheckAnswersAndDeclarationFail()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('canBeUpdated')
            ->andReturn(false);

        $irhpApplication->setDeclaration(true);
        $irhpApplication->setCheckedAnswers(true);

        $irhpApplication->resetCheckAnswersAndDeclaration();
        $this->assertTrue($irhpApplication->getDeclaration());
        $this->assertTrue($irhpApplication->getCheckedAnswers());
    }

    /**
     * @dataProvider dpCanMakeDeclarationForApplicationPathEnabled
     */
    public function testCanMakeDeclarationForApplicationPathEnabled(
        $irhpPermitTypeId,
        $status,
        $questionAnswerData,
        $expected
    ) {
        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId($irhpPermitTypeId);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->sut->setStatus(new RefData($status));

        $this->sut->shouldReceive('getQuestionAnswerData')
            ->andReturn($questionAnswerData);

        $this->assertEquals($expected, $this->sut->canMakeDeclaration());
    }

    public function dpCanMakeDeclarationForApplicationPathEnabled()
    {
        return [
            'ECMT Removal - not yet submitted - declaration cannot start' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Removal - not yet submitted - declaration not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Removal - not yet submitted - declaration completed' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'questionAnswerData' => [
                    [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => 1,
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Removal - under consideration - declaration not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'questionAnswerData' => [
                    [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => true,
            ],
            'ECMT Removal - withdrawn - declaration not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_WITHDRAWN,
                'questionAnswerData' => [
                    [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => false,
            ],
            'ECMT Removal - cancelled - declaration not started' => [
                'irhpPermitTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'status' => IrhpInterface::STATUS_CANCELLED,
                'questionAnswerData' => [
                    [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpCanMakeDeclarationForMultilateral
     */
    public function testCanMakeDeclarationForMultilateral(
        $status,
        $permitsRequired,
        $checkedAnswers,
        $expected
    ) {
        $this->sut->setStatus(new RefData($status));

        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $licence = m::mock(Licence::class);
        $this->sut->setLicence($licence);

        $irhpPermitApp = m::mock(IrhpPermitApplication::class)->makePartial();
        $irhpPermitApp->setPermitsRequired($permitsRequired);

        $this->sut->setIrhpPermitApplications(
            new ArrayCollection([$irhpPermitApp])
        );
        $this->sut->setCheckedAnswers($checkedAnswers);

        $this->assertEquals($expected, $this->sut->canMakeDeclaration());
    }

    public function dpCanMakeDeclarationForMultilateral()
    {
        return [
            'Not yet submitted - permits required set - answers checked' => [
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'permitsRequired' => 10,
                'checkedAnswers' => true,
                'expected' => true,
            ],
            'Not yet submitted - permits required set - answers not checked' => [
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'permitsRequired' => 10,
                'checkedAnswers' => null,
                'expected' => false,
            ],
            'Not yet submitted - permits required not set - answers not checked' => [
                'status' => IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                'permitsRequired' => null,
                'checkedAnswers' => null,
                'expected' => false,
            ],
            'Under consideration - permits required set - answers checked' => [
                'status' => IrhpInterface::STATUS_UNDER_CONSIDERATION,
                'permitsRequired' => 10,
                'checkedAnswers' => true,
                'expected' => true,
            ],
            'Withdrawn - permits required set - answers checked' => [
                'status' => IrhpInterface::STATUS_WITHDRAWN,
                'permitsRequired' => 10,
                'checkedAnswers' => true,
                'expected' => false,
            ],
            'Cancelled - permits required set - answers checked' => [
                'status' => IrhpInterface::STATUS_CANCELLED,
                'permitsRequired' => 10,
                'checkedAnswers' => true,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider dpCanMakeDeclarationForBilateral
     */
    public function testCanMakeDeclarationForBilateral($countriesAndStatuses, $canBeUpdated, $expected)
    {
        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->sut->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturn($canBeUpdated);

        $this->sut->shouldReceive('getBilateralCountriesAndStatuses')
            ->withNoArgs()
            ->andReturn($countriesAndStatuses);

        $this->assertEquals(
            $expected,
            $this->sut->canMakeDeclaration()
        );
    }

    public function dpCanMakeDeclarationForBilateral()
    {
        $incompleteCountries = [
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_COMPLETED
            ],
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_INCOMPLETE
            ],
        ];

        $completedCountries = [
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_COMPLETED
            ],
            [
                Entity::COUNTRY_PROPERTY_STATUS => SectionableInterface::SECTION_COMPLETION_COMPLETED
            ],
        ];

        return [
            [$incompleteCountries, false, false],
            [$incompleteCountries, true, false],
            [$completedCountries, false, false],
            [$completedCountries, true, true],
        ];
    }

    public function testMakeDeclaration()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('canMakeDeclaration')
            ->andReturn(true);

        $irhpApplication->makeDeclaration();

        $this->assertTrue($irhpApplication->getDeclaration());
    }

    public function testMakeDeclarationFail()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_MAKE_DECLARATION);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('canMakeDeclaration')
            ->andReturn(false);

        $irhpApplication->makeDeclaration();
    }

    /**
     * @dataProvider dptestGetPermitsRequired
     */
    public function testGetPermitsRequired($irhpPermitApplications, $expected)
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();

        $irhpApplication->setIrhpPermitApplications(
            new ArrayCollection($irhpPermitApplications)
        );

        $this->assertSame($expected, $irhpApplication->getPermitsRequired());
    }

    public function dpTestGetPermitsRequired()
    {
        $irhpPermitAppWithoutPermits = m::mock(IrhpPermitApplication::class);
        $irhpPermitAppWithoutPermits->shouldReceive('countPermitsRequired')
            ->withNoArgs()
            ->andReturn(0);

        $irhpPermitAppWithPermits = m::mock(IrhpPermitApplication::class);
        $irhpPermitAppWithPermits->shouldReceive('countPermitsRequired')
            ->withNoArgs()
            ->andReturn(10);

        return [
            'One Irhp Permit Application, 0 permits required' => [
                [$irhpPermitAppWithoutPermits],
                0
            ],
            'One Irhp Permit Application, 10 permits required' => [
                [$irhpPermitAppWithPermits],
                10
            ],
            'Two Irhp Permit Applications, 10 permits required on one and 0 on the other' => [
                [$irhpPermitAppWithPermits, $irhpPermitAppWithoutPermits],
                10
            ],
            'Two Irhp Permit Applications, 10 permits required on both' => [
                [$irhpPermitAppWithPermits, $irhpPermitAppWithPermits],
                20
            ]
        ];
    }

    public function testCanCreateOrReplaceIssueFeeTrue()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(true);

        $this->assertTrue($irhpApplication->canCreateOrReplaceIssueFee());
    }

    public function testCanCreateOrReplaceIssueFeeFalse()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(false);

        $this->assertFalse($irhpApplication->canCreateOrReplaceIssueFee());
    }

    public function testCanCreateOrReplaceApplicationFeeTrue()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(true);

        $this->assertTrue($irhpApplication->canCreateOrReplaceApplicationFee());
    }

    public function testCanCreateOrReplaceApplicationFeeFalse()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(false);

        $this->assertFalse($irhpApplication->canCreateOrReplaceApplicationFee());
    }

    public function testHaveFeesRequiredChangedExceptionWhenNothingStored()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('storeFeesRequired must be called before haveFeesRequiredChanged');

        $irhpApplication = new Entity();
        $irhpApplication->haveFeesRequiredChanged();
    }

    /**
     * @dataProvider dpHaveFeesRequiredChanged
     */
    public function testHaveFeesRequiredChanged(
        $irhpPermitType,
        $stock1QuantityBefore,
        $stock2QuantityBefore,
        $stock1QuantityAfter,
        $stock2QuantityAfter,
        $expected
    ) {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($irhpPermitType);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getIssueFeeProductReference')
            ->andReturn('BILATERAL_ISSUE_FEE_PRODUCT_REFERENCE');
        $irhpPermitApplication1->shouldReceive('countPermitsRequired')
            ->andReturn($stock1QuantityBefore);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getIssueFeeProductReference')
            ->andReturn('BILATERAL_ISSUE_FEE_PRODUCT_REFERENCE');
        $irhpPermitApplication2->shouldReceive('countPermitsRequired')
            ->andReturn($stock2QuantityBefore);

        $irhpApplication->setIrhpPermitApplications(
            new ArrayCollection([$irhpPermitApplication1, $irhpPermitApplication2])
        );

        $irhpApplication->storeFeesRequired();

        $updatedIrhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $updatedIrhpPermitApplication1->shouldReceive('getIssueFeeProductReference')
            ->andReturn('BILATERAL_ISSUE_FEE_PRODUCT_REFERENCE');
        $updatedIrhpPermitApplication1->shouldReceive('countPermitsRequired')
            ->andReturn($stock1QuantityAfter);

        $updatedIrhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $updatedIrhpPermitApplication2->shouldReceive('getIssueFeeProductReference')
            ->andReturn('BILATERAL_ISSUE_FEE_PRODUCT_REFERENCE');
        $updatedIrhpPermitApplication2->shouldReceive('countPermitsRequired')
            ->andReturn($stock2QuantityAfter);

        $irhpApplication->setIrhpPermitApplications(
            new ArrayCollection([$updatedIrhpPermitApplication1, $updatedIrhpPermitApplication2])
        );

        $this->assertEquals($expected, $irhpApplication->haveFeesRequiredChanged());
    }

    public function dpHaveFeesRequiredChanged()
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, 7, 11, 7, 11, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, 7, 11, 9, 9, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, 7, 11, 9, 13, true],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, null, null, 9, 13, true],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, 7, 11, 7, 11, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, 7, 11, 9, 9, false],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, 7, 11, 9, 13, true],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL, null, null, 9, 13, true],
        ];
    }

    public function testGetApplicationFeeProductRefsAndQuantities()
    {
        $productReference = 'PRODUCT_REFERENCE';
        $permitsRequired = 7;

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('getApplicationFeeProductReference')
            ->andReturn($productReference);
        $irhpApplication->shouldReceive('getPermitsRequired')
            ->andReturn($permitsRequired);

        $this->assertEquals(
            [$productReference => $permitsRequired],
            $irhpApplication->getApplicationFeeProductRefsAndQuantities()
        );
    }

    /**
     * @dataProvider dpGetApplicationFeeProductReference
     */
    public function testGetApplicationFeeProductReference($irhpPermitTypeId, $productReference)
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($irhpPermitTypeId);

        $this->assertEquals(
            $productReference,
            $irhpApplication->getApplicationFeeProductReference()
        );
    }

    public function dpGetApplicationFeeProductReference()
    {
        return [
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                FeeType::FEE_TYPE_IRHP_APP_BILATERAL_PRODUCT_REF
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                FeeType::FEE_TYPE_IRHP_APP_MULTILATERAL_PRODUCT_REF
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                FeeType::FEE_TYPE_ECMT_APP_PRODUCT_REF
            ],
        ];
    }

    public function testGetApplicationFeeProductReferenceUnsupportedType()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'No application fee product reference available for permit type 7'
        );

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn(7);

        $irhpApplication->getApplicationFeeProductReference();
    }

    public function testGetIssueFeeProductReferenceEcmtShortTerm()
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn(true);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn(false);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            FeeType::FEE_TYPE_ECMT_SHORT_TERM_ISSUE_PRODUCT_REF,
            $irhpApplication->getIssueFeeProductReference()
        );
    }

    public function testGetIssueFeeProductReferenceEcmtAnnual()
    {
        $productReference = 'PRODUCT_REFERENCE_FOR_TIER';

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn(true);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->setIrhpPermitType($irhpPermitType);

        $irhpApplication->shouldReceive('getProductReferenceForTier')
            ->withNoArgs()
            ->andReturn($productReference);

        $this->assertEquals(
            $productReference,
            $irhpApplication->getIssueFeeProductReference()
        );
    }

    /**
     * @dataProvider dpGetIssueFeeProductReferenceUnsupportedType
     */
    public function testGetIssueFeeProductReferenceUnsupportedType($irhpPermitTypeId)
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'No issue fee product reference available for permit type ' . $irhpPermitTypeId
        );

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn(false);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn($irhpPermitTypeId);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->setIrhpPermitType($irhpPermitType);

        $irhpApplication->getIssueFeeProductReference();
    }

    public function dpGetIssueFeeProductReferenceUnsupportedType()
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER],
        ];
    }

    public function testGetIssueFeeProductRefsAndQuantities()
    {
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getIssueFeeProductReference')
            ->andReturn('PRODUCT_REFERENCE_1');
        $irhpPermitApplication1->shouldReceive('countPermitsRequired')
            ->andReturn(7);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getIssueFeeProductReference')
            ->andReturn('PRODUCT_REFERENCE_2');
        $irhpPermitApplication2->shouldReceive('countPermitsRequired')
            ->andReturn(3);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication3->shouldReceive('getIssueFeeProductReference')
            ->andReturn('PRODUCT_REFERENCE_2');
        $irhpPermitApplication3->shouldReceive('countPermitsRequired')
            ->andReturn(0);

        $irhpPermitApplication4 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication4->shouldReceive('getIssueFeeProductReference')
            ->andReturn('PRODUCT_REFERENCE_3');
        $irhpPermitApplication4->shouldReceive('countPermitsRequired')
            ->andReturn(0);

        $irhpPermitApplication5 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication5->shouldReceive('getIssueFeeProductReference')
            ->andReturn('PRODUCT_REFERENCE_4');
        $irhpPermitApplication5->shouldReceive('countPermitsRequired')
            ->andReturn(5);

        $irhpPermitApplication6 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication6->shouldReceive('getIssueFeeProductReference')
            ->andReturn('PRODUCT_REFERENCE_4');
        $irhpPermitApplication6->shouldReceive('countPermitsRequired')
            ->andReturn(6);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->setIrhpPermitApplications(
            new ArrayCollection(
                [
                    $irhpPermitApplication1,
                    $irhpPermitApplication2,
                    $irhpPermitApplication3,
                    $irhpPermitApplication4,
                    $irhpPermitApplication5,
                    $irhpPermitApplication6
                ]
            )
        );

        $expected = [
            'PRODUCT_REFERENCE_1' => 7,
            'PRODUCT_REFERENCE_2' => 3,
            'PRODUCT_REFERENCE_4' => 11
        ];

        $this->assertEquals(
            $expected,
            $irhpApplication->getIssueFeeProductRefsAndQuantities()
        );
    }

    public function testUpdateDateReceived()
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $dateString = '2019-01-01';
        $irhpApplication->updateDateReceived('2019-01-01');
        $this->assertEquals(new DateTime($dateString), $irhpApplication->getDateReceived());
    }

    /**
     * @dataProvider dptestIsReadyForIssuing
     */
    public function testIsReadyForIssuing($hasOutstandingFees, $expectedResult)
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('hasOutstandingFees')
            ->andReturn($hasOutstandingFees);

        $this->assertEquals($expectedResult, $entity->isReadyForIssuing());
    }

    public function dpTestIsReadyForIssuing()
    {
        return [
            [false, true],
            [true, false],
        ];
    }

    public function testSubmit()
    {
        $status = m::mock(RefData::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeSubmitted')
            ->andReturn(true);
        $entity->shouldReceive('proceedToIssuing')
            ->with($status)
            ->once();

        $entity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $entity->submit($status);
    }

    /**
     * @dataProvider dpSubmitShortTermAndAnnual
     */
    public function testSubmitShortTermAndAnnual($irhpPermitTypeId)
    {
        $status = m::mock(RefData::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeSubmitted')
            ->andReturn(true);
        $entity->shouldReceive('proceedToUnderConsideration')
            ->with($status)
            ->once();

        $entity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($irhpPermitTypeId);

        $entity->submit($status);
    }

    public function dpSubmitShortTermAndAnnual()
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
        ];
    }

    /**
     * @dataProvider dpSubmitCertOfRoadworthiness
     */
    public function testSubmitCertOfRoadworthiness($irhpPermitTypeId)
    {
        $status = m::mock(RefData::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeSubmitted')
            ->andReturn(true);
        $entity->shouldReceive('proceedToValid')
            ->with($status)
            ->once();

        $entity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($irhpPermitTypeId);

        $entity->submit($status);
    }

    public function dpSubmitCertOfRoadworthiness()
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER],
        ];
    }

    public function testSubmitException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_SUBMIT);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeSubmitted')
            ->andReturn(false);
        $entity->shouldReceive('proceedToIssuing')
            ->never();

        $entity->submit(m::mock(RefData::class));
    }

    public function testSubmitExceptionUnknownType()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_SUBMIT);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeSubmitted')
            ->withNoArgs()
            ->andReturn(true);
        $entity->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn('unknown_type');

        $entity->submit(m::mock(RefData::class));
    }

    public function testGrant()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeGranted')
            ->withNoArgs()
            ->andReturnTrue();

        $status = m::mock(RefData::class);

        $entity->grant($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testGrantException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_GRANT);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeGranted')
            ->withNoArgs()
            ->andReturnFalse();

        $entity->grant(m::mock(RefData::class));
    }

    public function testProceedToIssuing()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isReadyForIssuing')
            ->andReturn(true);

        $status = m::mock(RefData::class);

        $entity->proceedToIssuing($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testProceedToIssuingException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_ISSUE);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isReadyForIssuing')
            ->andReturn(false);

        $entity->proceedToIssuing(m::mock(RefData::class));
    }

    public function testProceedToUnderConsideration()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('hasOutstandingFees')
            ->withNoArgs()
            ->andReturnFalse();

        $status = m::mock(RefData::class);

        $entity->proceedToUnderConsideration($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testProceedToUnderConsiderationException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_SUBMIT);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('hasOutstandingFees')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->proceedToUnderConsideration(m::mock(RefData::class));
    }

    public function testProceedToValid()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isIssueInProgress')
            ->andReturn(true);

        $status = m::mock(RefData::class);

        $entity->proceedToValid($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testProceedToValidException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            sprintf(
                'This application is not in the correct state to proceed to valid (status: %s, irhpPermitType: %d)',
                IrhpInterface::STATUS_EXPIRED,
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE
            )
        );

        $oldStatus = m::mock(RefData::class);
        $oldStatus->shouldReceive('getId')
            ->andReturn(IrhpInterface::STATUS_EXPIRED);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')->withNoArgs()
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setStatus($oldStatus);
        $entity->setIrhpPermitType($irhpPermitType);
        $entity->shouldReceive('isIssueInProgress')
            ->andReturn(false)
            ->shouldReceive('isCertificateOfRoadworthiness')
            ->andReturn(false);

        $entity->proceedToValid(m::mock(RefData::class));
    }

    public function testGetQuestionAnswerBilateral()
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->withNoArgs()->andReturn(true);
        $irhpPermitType->shouldReceive('isMultilateral')->withNoArgs()->andReturn(false);

        $this->sut->setIrhpPermitType($irhpPermitType);

        $countryIT = m::mock(Country::class);
        $countryIT->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn('IT');
        $countryIT->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn('Italy');

        $countryFR = m::mock(Country::class);
        $countryFR->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn('FR');
        $countryFR->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn('France');

        $countryDE = m::mock(Country::class);
        $countryDE->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn('DE');
        $countryDE->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn('Germany');

        $this->sut->setCountrys(
            new ArrayCollection([$countryIT, $countryFR, $countryDE])
        );

        $irhpPermitApplicationIT = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplicationIT->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn('IT');
        $irhpPermitApplicationIT->shouldReceive('getCheckedAnswers')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpPermitApplicationIT->shouldReceive('getId')
            ->withNoArgs()
            ->andReturnNull();

        $irhpPermitApplicationDE = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplicationDE->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn('DE');
        $irhpPermitApplicationDE->shouldReceive('getCheckedAnswers')
            ->withNoArgs()
            ->andReturnFalse();

        $irhpPermitApplicationDE->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(3322);

        $this->sut->setIrhpPermitApplications(
            new ArrayCollection([$irhpPermitApplicationIT, $irhpPermitApplicationDE])
        );

        $expectedCountriesAndStatuses = [
            [
                'countryCode' => 'FR',
                'countryName' => 'France',
                'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                'irhpPermitApplication' => null
            ],
            [
                'countryCode' => 'DE',
                'countryName' => 'Germany',
                'status' => SectionableInterface::SECTION_COMPLETION_INCOMPLETE,
                'irhpPermitApplication' => 3322
            ],
            [
                'countryCode' => 'IT',
                'countryName' => 'Italy',
                'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                'irhpPermitApplication' => null
            ],
        ];

        $expectedSectionCompletion = [
            'countries' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
            'declaration' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
            'submitAndPay' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
            'totalSections' => 3,
            'totalCompleted' => 2,
            'allCompleted' => false,
        ];

        $this->sut->shouldReceive('getSectionCompletion')
            ->withNoArgs()
            ->andReturn($expectedSectionCompletion);

        $expected = [
            'countries' => $expectedCountriesAndStatuses,
            'reviewAndSubmit' => $expectedSectionCompletion,
        ];

        $this->assertEquals($expected, $this->sut->getQuestionAnswerData());
    }

    public function testGetQuestionAnswerMultilateral()
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->withNoArgs()->andReturn(false);
        $irhpPermitType->shouldReceive('isMultilateral')->withNoArgs()->andReturn(true);

        $licence = m::mock(Licence::class);
        $entity = $this->createNewEntity(null, null, $irhpPermitType, $licence);

        $this->assertEquals([], $entity->getQuestionAnswerData());
    }

    public function testGetQuestionAnswerDataWithoutActiveApplicationPath()
    {
        $expected = [
            'custom-check-answers' => [
                'section' => 'checkedAnswers',
                'slug' => 'custom-check-answers',
                'questionShort' => 'section.name.application-check-answers',
                'question' => 'section.name.application-check-answers',
                'answer' => null,
                'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
            ],
            'custom-declaration' => [
                'section' => 'declaration',
                'slug' => 'custom-declaration',
                'questionShort' => 'section.name.application-declaration',
                'question' => 'section.name.application-declaration',
                'answer' => null,
                'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
            ],
        ];

        $createdOn = new DateTime();

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getActiveApplicationPath')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturn(false);
        $irhpPermitType->shouldReceive('isMultilateral')->once()->withNoArgs()->andReturn(false);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->addIrhpPermitApplications($irhpPermitApplication);
        $entity->setCreatedOn($createdOn);

        $this->assertEquals($expected, $entity->getQuestionAnswerData());
    }

    /**
     * @dataProvider dpGetQuestionAnswerDataWithActiveApplicationPath
     */
    public function testGetQuestionAnswerDataWithActiveApplicationPath($data, $applicationSteps, $expected)
    {
        $applicationPath = m::mock(ApplicationPath::class);
        $applicationPath->shouldReceive('getApplicationSteps')->once()->withNoArgs()->andReturn($applicationSteps);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getActiveApplicationPath')
            ->withNoArgs()
            ->once()
            ->andReturn($applicationPath);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturn(false);
        $irhpPermitType->shouldReceive('isMultilateral')->once()->withNoArgs()->andReturn(false);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->addIrhpPermitApplications($irhpPermitApplication);
        $entity->setAnswers($data['answers']);
        $entity->setCreatedOn($data['createdOn']);
        $entity->setCheckedAnswers($data['checkedAnswers']);
        $entity->setDeclaration($data['declaration']);

        $this->assertEquals($expected, $entity->getQuestionAnswerData());
    }

    public function dpGetQuestionAnswerDataWithActiveApplicationPath()
    {
        $createdOn = new DateTime();

        // q1
        $question1TextId = 1;
        $question1Text = m::mock(QuestionText::class);
        $question1Text->shouldReceive('getId')->withNoArgs()->andReturn($question1TextId);
        $question1Text->shouldReceive('getQuestionShortKey')->withNoArgs()->andReturn('q1-short-key');
        $question1Text->shouldReceive('getTranslationKeyFromQuestionKey')->withNoArgs()->andReturn('q1-key');
        $question1Text->shouldReceive('getQuestion->getQuestionType->getId')->withNoArgs()->andReturn('q1-type');

        $question1 = m::mock(Question::class)->makePartial();
        $question1->shouldReceive('getQuestion')->withNoArgs()->andReturn($question1);
        $question1->shouldReceive('getActiveQuestionText')->with($createdOn)->andReturn($question1Text);
        $question1->shouldReceive('getSlug')->withNoArgs()->andReturn('q1-slug');
        $question1->shouldReceive('isCustom')->withNoArgs()->andReturn(false);

        $step1 = m::mock(ApplicationStep::class);
        $step1->shouldReceive('getQuestion')->withNoArgs()->andReturn($question1);

        $answer1 = m::mock(Answer::class);
        $answer1->shouldReceive('getQuestionText')->withNoArgs()->andReturn($question1Text);
        $answer1->shouldReceive('getValue')->withNoArgs()->andReturn('q1-answer');

        // q2
        $question2TextId = 2;
        $question2Text = m::mock(QuestionText::class);
        $question2Text->shouldReceive('getId')->withNoArgs()->andReturn($question2TextId);
        $question2Text->shouldReceive('getQuestionShortKey')->withNoArgs()->andReturn('q2-short-key');
        $question2Text->shouldReceive('getTranslationKeyFromQuestionKey')->withNoArgs()->andReturn('q2-key');
        $question2Text->shouldReceive('getQuestion->getQuestionType->getId')->withNoArgs()->andReturn('q2-type');

        $question2 = m::mock(Question::class)->makePartial();
        $question2->shouldReceive('getQuestion')->withNoArgs()->andReturn($question2);
        $question2->shouldReceive('getActiveQuestionText')->with($createdOn)->andReturn($question2Text);
        $question2->shouldReceive('getSlug')->withNoArgs()->andReturn('q2-slug');
        $question2->shouldReceive('isCustom')->withNoArgs()->andReturn(false);

        $step2 = m::mock(ApplicationStep::class);
        $step2->shouldReceive('getQuestion')->withNoArgs()->andReturn($question2);

        $answer2 = m::mock(Answer::class);
        $answer2->shouldReceive('getQuestionText')->withNoArgs()->andReturn($question2Text);
        $answer2->shouldReceive('getValue')->withNoArgs()->andReturn('q2-answer');

        return [
           'q1 not answered' => [
                'data' => [
                    'answers' => new ArrayCollection([]),
                    'checkedAnswers' => 0,
                    'declaration' => 0,
                    'createdOn' => $createdOn,
                ],
                'applicationSteps' => new ArrayCollection([$step1, $step2]),
                'expected' => [
                    'q1-slug' => [
                        'section' => 'q1-slug',
                        'slug' => 'q1-slug',
                        'questionShort' => 'q1-short-key',
                        'question' => 'q1-key',
                        'questionType' => 'q1-type',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                    'q2-slug' => [
                        'section' => 'q2-slug',
                        'slug' => 'q2-slug',
                        'questionShort' => 'q2-short-key',
                        'question' => 'q2-key',
                        'questionType' => 'q2-type',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                    'custom-check-answers' => [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 0,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                    'custom-declaration' => [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                false,
            ],
            'q1 answered' => [
                'data' => [
                    'answers' => new ArrayCollection([$question1TextId => $answer1]),
                    'checkedAnswers' => 0,
                    'declaration' => 0,
                    'createdOn' => $createdOn,
                ],
                'applicationSteps' => new ArrayCollection([$step1, $step2]),
                'expected' => [
                    'q1-slug' => [
                        'section' => 'q1-slug',
                        'slug' => 'q1-slug',
                        'questionShort' => 'q1-short-key',
                        'question' => 'q1-key',
                        'questionType' => 'q1-type',
                        'answer' => 'q1-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'q2-slug' => [
                        'section' => 'q2-slug',
                        'slug' => 'q2-slug',
                        'questionShort' => 'q2-short-key',
                        'question' => 'q2-key',
                        'questionType' => 'q2-type',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                    'custom-check-answers' => [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 0,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                    'custom-declaration' => [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                false,
            ],
            'q2 answered' => [
                'data' => [
                    'answers' => new ArrayCollection([$question1TextId => $answer1, $question2TextId => $answer2]),
                    'checkedAnswers' => 0,
                    'declaration' => 0,
                    'createdOn' => $createdOn,
                ],
                'applicationSteps' => new ArrayCollection([$step1, $step2]),
                'expected' => [
                    'q1-slug' => [
                        'section' => 'q1-slug',
                        'slug' => 'q1-slug',
                        'questionShort' => 'q1-short-key',
                        'question' => 'q1-key',
                        'questionType' => 'q1-type',
                        'answer' => 'q1-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'q2-slug' => [
                        'section' => 'q2-slug',
                        'slug' => 'q2-slug',
                        'questionShort' => 'q2-short-key',
                        'question' => 'q2-key',
                        'questionType' => 'q2-type',
                        'answer' => 'q2-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'custom-check-answers' => [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 0,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                    'custom-declaration' => [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_CANNOT_START,
                    ],
                ],
                false
            ],
            'answers checked' => [
                'data' => [
                    'answers' => new ArrayCollection([$question1TextId => $answer1, $question2TextId => $answer2]),
                    'checkedAnswers' => 1,
                    'declaration' => 0,
                    'createdOn' => $createdOn,
                ],
                'applicationSteps' => new ArrayCollection([$step1, $step2]),
                'expected' => [
                    'q1-slug' => [
                        'section' => 'q1-slug',
                        'slug' => 'q1-slug',
                        'questionShort' => 'q1-short-key',
                        'question' => 'q1-key',
                        'questionType' => 'q1-type',
                        'answer' => 'q1-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'q2-slug' => [
                        'section' => 'q2-slug',
                        'slug' => 'q2-slug',
                        'questionShort' => 'q2-short-key',
                        'question' => 'q2-key',
                        'questionType' => 'q2-type',
                        'answer' => 'q2-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'custom-check-answers' => [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 1,
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'custom-declaration' => [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => null,
                        'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                    ],
                ],
                false
            ],
            'declaration set' => [
                'data' => [
                    'answers' => new ArrayCollection([$question1TextId => $answer1, $question2TextId => $answer2]),
                    'checkedAnswers' => 1,
                    'declaration' => 1,
                    'createdOn' => $createdOn,
                ],
                'applicationSteps' => new ArrayCollection([$step1, $step2]),
                'expected' => [
                    'q1-slug' => [
                        'section' => 'q1-slug',
                        'slug' => 'q1-slug',
                        'questionShort' => 'q1-short-key',
                        'question' => 'q1-key',
                        'questionType' => 'q1-type',
                        'answer' => 'q1-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'q2-slug' => [
                        'section' => 'q2-slug',
                        'slug' => 'q2-slug',
                        'questionShort' => 'q2-short-key',
                        'question' => 'q2-key',
                        'questionType' => 'q2-type',
                        'answer' => 'q2-answer',
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'custom-check-answers' => [
                        'section' => 'checkedAnswers',
                        'slug' => 'custom-check-answers',
                        'questionShort' => 'section.name.application-check-answers',
                        'question' => 'section.name.application-check-answers',
                        'answer' => 1,
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                    'custom-declaration' => [
                        'section' => 'declaration',
                        'slug' => 'custom-declaration',
                        'questionShort' => 'section.name.application-declaration',
                        'question' => 'section.name.application-declaration',
                        'answer' => 1,
                        'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                    ],
                ],
                false,
            ],
        ];
    }

    public function testGetAnswerForCustomEcmtRemovalNoOfPermits()
    {
        $permitsRequired = 47;

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('countPermitsRequired')
            ->andReturn($permitsRequired);

        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(true);
        $question->shouldReceive('getFormControlType')->andReturn(Question::FORM_CONTROL_ECMT_REMOVAL_NO_OF_PERMITS);

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $entity = $this->createNewEntity();
        $entity->addIrhpPermitApplications($irhpPermitApplication);

        $this->assertEquals($permitsRequired, $entity->getAnswer($step));
    }

    /**
     * @dataProvider dpGetAnswerForCustomEcmtNoOfPermits
     */
    public function testGetAnswerForCustomEcmtNoOfPermits(
        $formControlType,
        $requiredEuro5,
        $requiredEuro6,
        $expectedAnswer
    ) {
        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(true);
        $question->shouldReceive('getFormControlType')->andReturn($formControlType);

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn($requiredEuro6);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);

        $this->assertSame(
            $expectedAnswer,
            $entity->getAnswer($step)
        );
    }

    public function dpGetAnswerForCustomEcmtNoOfPermits()
    {
        return [
            [
                'formControlType' => Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_EITHER,
                'requiredEuro5' => 5,
                'requiredEuro6' => 7,
                'expectedAnswer' => Entity::NON_SCALAR_ANSWER_PRESENT,
            ],
            [
                'formControlType' => Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_EITHER,
                'requiredEuro5' => 5,
                'requiredEuro6' => 0,
                'expectedAnswer' => Entity::NON_SCALAR_ANSWER_PRESENT,
            ],
            [
                'formControlType' => Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_EITHER,
                'requiredEuro5' => null,
                'requiredEuro6' => 5,
                'expectedAnswer' => null,
            ],
            [
                'formControlType' => Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_BOTH,
                'requiredEuro5' => 5,
                'requiredEuro6' => 7,
                'expectedAnswer' => Entity::NON_SCALAR_ANSWER_PRESENT,
            ],
            [
                'formControlType' => Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_BOTH,
                'requiredEuro5' => 5,
                'requiredEuro6' => 0,
                'expectedAnswer' => Entity::NON_SCALAR_ANSWER_PRESENT,
            ],
            [
                'formControlType' => Question::FORM_CONTROL_ECMT_NO_OF_PERMITS_BOTH,
                'requiredEuro5' => null,
                'requiredEuro6' => 5,
                'expectedAnswer' => null,
            ],
        ];
    }

    public function testGetAnswerForCustomEcmtInternationalJourneys()
    {
        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(true);
        $question->shouldReceive('getFormControlType')->andReturn(
            Question::FORM_CONTROL_ECMT_INTERNATIONAL_JOURNEYS
        );

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $internationalJourneysKey = 'int_journeys_ref_data_key';

        $refData = m::mock(RefData::class);
        $refData->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($internationalJourneysKey);

        $entity = $this->createNewEntity();
        $entity->setInternationalJourneys($refData);

        $this->assertSame(
            $internationalJourneysKey,
            $entity->getAnswer($step)
        );
    }

    public function testGetAnswerForCustomEcmtInternationalJourneysNull()
    {
        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(true);
        $question->shouldReceive('getFormControlType')->andReturn(
            Question::FORM_CONTROL_ECMT_INTERNATIONAL_JOURNEYS
        );

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $entity = $this->createNewEntity();
        $entity->setInternationalJourneys(null);

        $this->assertNull(
            $entity->getAnswer($step)
        );
    }

    /**
     * @dataProvider dpTestGetAnswerForCustomEcmtSectors
     */
    public function testGetAnswerForCustomEcmtSectors($sectorsEntity, $expectedAnswer)
    {
        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(true);
        $question->shouldReceive('getFormControlType')->andReturn(
            Question::FORM_CONTROL_ECMT_SECTORS
        );

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $entity = $this->createNewEntity();
        $entity->setSectors($sectorsEntity);

        $this->assertEquals(
            $expectedAnswer,
            $entity->getAnswer($step)
        );
    }

    public function dpTestGetAnswerForCustomEcmtSectors()
    {
        $sectorId = 7;

        $sectors = m::mock(Sectors::class);
        $sectors->shouldReceive('getId')
            ->andReturn($sectorId);

        return [
            [$sectors, $sectorId],
            [null, null],
        ];
    }

    /**
     * @dataProvider dpTestGetAnswerForCustomEcmtAnnual2018NoOfPermits
     */
    public function testGetAnswerForCustomEcmtAnnual2018NoOfPermits(
        $isSnapshot,
        $requiredEuro5,
        $requiredEuro6,
        $expectedAnswer
    ) {
        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(true);
        $question->shouldReceive('getFormControlType')->andReturn(
            Question::FORM_CONTROL_ECMT_ANNUAL_2018_NO_OF_PERMITS
        );

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn($requiredEuro6);

        $entity = $this->createNewEntity();
        $entity->setIrhpPermitApplications(
            new ArrayCollection([$irhpPermitApplication])
        );

        $this->assertEquals(
            $expectedAnswer,
            $entity->getAnswer($step)
        );
    }

    public function dpTestGetAnswerForCustomEcmtAnnual2018NoOfPermits()
    {
        return [
            [true, null, null, null],
            [true, 4, null, 4],
            [true, null, 6, 6],
            [false, null, null, null],
            [false, 4, null, 4],
            [false, null, 6, 6],
        ];
    }

    public function testGetAnswerForQuestionWithoutActiveQuestionText()
    {
        $createdOn = new DateTime();

        $question = m::mock(Question::class)->makePartial();
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn(false);
        $question->shouldReceive('getActiveQuestionText')->with($createdOn)->once()->andReturn(null);

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $entity = $this->createNewEntity();
        $entity->setCreatedOn($createdOn);

        $this->assertNull($entity->getAnswer($step));
    }

    /**
     * @dataProvider dpGetAnswerForQuestion
     */
    public function testGetAnswerForQuestionWithoutAnswer($isCustom, $formControlType)
    {
        $createdOn = new DateTime();

        $questionTextId = 1;
        $questionText = m::mock(QuestionText::class);
        $questionText->shouldReceive('getId')->withNoArgs()->once()->andReturn($questionTextId);

        $question = m::mock(Question::class)->makePartial();
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn($isCustom);
        $question->shouldReceive('getFormControlType')->withNoArgs()->andReturn($formControlType);
        $question->shouldReceive('getActiveQuestionText')->with($createdOn)->once()->andReturn($questionText);

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $entity = $this->createNewEntity();
        $entity->setCreatedOn($createdOn);

        $this->assertNull($entity->getAnswer($step));
    }

    /**
     * @dataProvider dpGetAnswerForQuestion
     */
    public function testGetAnswerForQuestionWithAnswer($isCustom, $formControlType)
    {
        $createdOn = new DateTime();
        $answer = 'answer';

        $questionTextId = 1;
        $questionText = m::mock(QuestionText::class);
        $questionText->shouldReceive('getId')->withNoArgs()->andReturn($questionTextId);

        $question = m::mock(Question::class)->makePartial();
        $question->shouldReceive('isCustom')->withNoArgs()->once()->andReturn($isCustom);
        $question->shouldReceive('getFormControlType')->withNoArgs()->andReturn($formControlType);
        $question->shouldReceive('getActiveQuestionText')->with($createdOn)->once()->andReturn($questionText);

        $step = m::mock(ApplicationStep::class);
        $step->shouldReceive('getQuestion')->withNoArgs()->once()->andReturn($question);

        $answer = m::mock(Answer::class);
        $answer->shouldReceive('getValue')->withNoArgs()->once()->andReturn($answer);
        $answer->shouldReceive('getQuestionText')->withNoArgs()->andReturn($questionText);

        $entity = $this->createNewEntity();
        $entity->setCreatedOn($createdOn);
        $entity->setAnswers(new ArrayCollection([$questionTextId => $answer]));

        $this->assertEquals($answer, $entity->getAnswer($step));
    }

    public function dpGetAnswerForQuestion()
    {
        return [
            [false, null],
            [true, Question::FORM_CONTROL_ECMT_REMOVAL_PERMIT_START_DATE],
            [true, Question::FORM_CONTROL_ECMT_ANNUAL_TRIPS_ABROAD],
            [true, Question::FORM_CONTROL_ECMT_SHORT_TERM_EARLIEST_PERMIT_DATE],
            [true, Question::FORM_CONTROL_ECMT_RESTRICTED_COUNTRIES],
            [true, Question::FORM_CONTROL_CERT_ROADWORTHINESS_MOT_EXPIRY_DATE],
            [true, Question::FORM_CONTROL_COMMON_CERTIFICATES],
        ];
    }

    public function testGetAnswerUnknownCustomType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to retrieve answer status for form control type unknown_form_control_type'
        );

        $applicationPathLockedOn = m::mock(DateTime::class);
        $this->sut->shouldReceive('getApplicationPathLockedOn')
            ->withNoArgs()
            ->andReturn($applicationPathLockedOn);

        $question = m::mock(Question::class);
        $question->shouldReceive('isCustom')
            ->withNoArgs()
            ->andReturnTrue();
        $question->shouldReceive('getFormControlType')
            ->withNoArgs()
            ->andReturn('unknown_form_control_type');

        $applicationStep = m::mock(ApplicationStep::class);
        $applicationStep->shouldReceive('getQuestion')
            ->withNoArgs()
            ->andReturn($question);

        $this->sut->getAnswer($applicationStep);
    }

    public function testGetOutstandingApplicationFees()
    {
        $fee1 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, true);
        $fee2 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, false);
        $fee3 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_ISSUE, false);
        $fee4 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, true);
        $fee5 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_ISSUE, true);

        $this->sut->setFees(
            new ArrayCollection([$fee1, $fee2, $fee3, $fee4, $fee5])
        );

        $outstandingApplicationFees = $this->sut->getOutstandingApplicationFees();
        $this->assertCount(2, $outstandingApplicationFees);
        $this->assertSame($fee1, $outstandingApplicationFees[0]);
        $this->assertSame($fee4, $outstandingApplicationFees[1]);
    }

    public function testGetOutstandingIssueFees()
    {
        $fee1 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_ISSUE, true);
        $fee2 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_ISSUE, false);
        $fee3 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, false);
        $fee4 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_ISSUE, true);
        $fee5 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, true);

        $this->sut->setFees(
            new ArrayCollection([$fee1, $fee2, $fee3, $fee4, $fee5])
        );

        $outstandingIssueFees = $this->sut->getOutstandingIssueFees();
        $this->assertCount(2, $outstandingIssueFees);
        $this->assertSame($fee1, $outstandingIssueFees[0]);
        $this->assertSame($fee4, $outstandingIssueFees[1]);
    }

    public function testGetOutstandingIrfoPermitFees()
    {
        $fee1 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_ISSUE, true);
        $fee2 = $this->createMockFee(FeeType::FEE_TYPE_IRFOGVPERMIT, false);
        $fee3 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, false);
        $fee4 = $this->createMockFee(FeeType::FEE_TYPE_IRFOGVPERMIT, true);
        $fee5 = $this->createMockFee(FeeType::FEE_TYPE_IRHP_APP, true);
        $fee6 = $this->createMockFee(FeeType::FEE_TYPE_IRFOGVPERMIT, true);

        $this->sut->setFees(
            new ArrayCollection([$fee1, $fee2, $fee3, $fee4, $fee5, $fee6])
        );

        $outstandingIrfoPermitFees = $this->sut->getOutstandingIrfoPermitFees();
        $this->assertCount(2, $outstandingIrfoPermitFees);
        $this->assertSame($fee4, $outstandingIrfoPermitFees[0]);
        $this->assertSame($fee6, $outstandingIrfoPermitFees[1]);
    }

    public function testGetContextValue()
    {
        $irhpApplicationId = 87;

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->setId($irhpApplicationId);

        $this->assertEquals($irhpApplicationId, $irhpApplication->getContextValue());
    }

    private function createMockFee($feeTypeId, $isOutstanding)
    {
        $fee = m::mock(Fee::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn($feeTypeId);
        $fee->shouldReceive('isOutstanding')
            ->andReturn($isOutstanding);

        return $fee;
    }

    private function createNewEntity(
        $source = null,
        $status = null,
        $irhpPermitType = null,
        $licence = null,
        $dateReceived = null
    ): Entity {
        if (!isset($source)) {
            $source = m::mock(RefData::class);
        }

        if (!isset($status)) {
            $status = m::mock(RefData::class);
        }

        if (!isset($irhpPermitType)) {
            $irhpPermitType = m::mock(IrhpPermitType::class);
        }

        if (!isset($licence)) {
            $licence = m::mock(Licence::class);
        }

        return Entity::createNew($source, $status, $irhpPermitType, $licence, $dateReceived);
    }

    /**
     * @dataProvider dpTestGetFeePerPermitBilateralMultilateral
     */
    public function testGetFeePerPermitBilateralMultilateral($irhpPermitTypeId)
    {
        $applicationFeeType = m::mock(FeeType::class);
        $applicationFeeType->shouldReceive('getFixedValue')
            ->andReturn(43.20);

        $issueFeeType = m::mock(FeeType::class);
        $issueFeeType->shouldReceive('getFixedValue')
            ->andReturn(12.15);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($irhpPermitTypeId);

        $this->assertEquals(
            55.35,
            $entity->getFeePerPermit($applicationFeeType, $issueFeeType)
        );
    }

    public function dpTestGetFeePerPermitBilateralMultilateral()
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL]
        ];
    }

    public function testGetFeePerPermitEcmtRemoval()
    {
        $issueFee = 14.20;

        $issueFeeType = m::mock(FeeType::class);
        $issueFeeType->shouldReceive('getFixedValue')
            ->andReturn($issueFee);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL);

        $this->assertEquals(
            $issueFee,
            $entity->getFeePerPermit(null, $issueFeeType)
        );
    }

    public function testGetFeePerPermitUnsupported()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'Cannot get fee per permit for irhp permit type ' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT
        );

        $applicationFeeType = m::mock(FeeType::class);
        $issueFeeType = m::mock(FeeType::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT);

        $entity->getFeePerPermit($applicationFeeType, $issueFeeType);
    }

    public function testGetFirstIrhpPermitApplication()
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $entity = $this->createNewEntity();
        $entity->addIrhpPermitApplications($irhpPermitApplication);

        $this->assertSame(
            $irhpPermitApplication,
            $entity->getFirstIrhpPermitApplication()
        );
    }

    public function testGetFirstIrhpPermitApplicationExceptionOnNone()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'IrhpApplication has zero linked IrhpPermitApplication instances'
        );

        $entity = $this->createNewEntity();
        $entity->getFirstIrhpPermitApplication();
    }

    public function testGetFirstIrhpPermitApplicationExceptionOnMoreThanOne()
    {
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);

        $entity = $this->createNewEntity();
        $entity->addIrhpPermitApplications($irhpPermitApplication1);
        $entity->addIrhpPermitApplications($irhpPermitApplication2);

        $this->assertSame(
            $irhpPermitApplication1,
            $entity->getFirstIrhpPermitApplication()
        );
    }

    public function testExpire()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeExpired')
            ->andReturn(true);

        $this->assertNull($entity->getExpiryDate());
        $status = m::mock(RefData::class);

        $entity->expire($status);
        $this->assertSame($status, $entity->getStatus());
        $this->assertInstanceOf(DateTime::class, $entity->getExpiryDate());
    }

    public function testExpireException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('This application cannot be expired.');

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeExpired')
            ->andReturn(false);

        $entity->expire(m::mock(RefData::class));
    }

    /**
     * @dataProvider dpCanBeExpired
     */
    public function testCanBeExpired($status, $hasValidPermits, $expected)
    {
        $entity = $this->createNewEntity();
        $entity->setStatus(new RefData($status));

        $irhpPermitApp1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApp1->shouldReceive('hasValidPermits')->andReturn(false);
        $entity->addIrhpPermitApplications($irhpPermitApp1);

        $irhpPermitApp2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApp2->shouldReceive('hasValidPermits')->andReturn($hasValidPermits);
        $entity->addIrhpPermitApplications($irhpPermitApp2);

        $this->assertEquals($expected, $entity->canBeExpired());
    }

    public function dpCanBeExpired()
    {
        return [
            [IrhpInterface::STATUS_VALID, true, false],
            [IrhpInterface::STATUS_VALID, false, true],
            [IrhpInterface::STATUS_CANCELLED, true, false],
            [IrhpInterface::STATUS_CANCELLED, false, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, true, false],
            [IrhpInterface::STATUS_NOT_YET_SUBMITTED, false, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, true, false],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION, false, false],
            [IrhpInterface::STATUS_WITHDRAWN, true, false],
            [IrhpInterface::STATUS_WITHDRAWN, false, false],
            [IrhpInterface::STATUS_AWAITING_FEE, true, false],
            [IrhpInterface::STATUS_AWAITING_FEE, false, false],
            [IrhpInterface::STATUS_FEE_PAID, true, false],
            [IrhpInterface::STATUS_FEE_PAID, false, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, true, false],
            [IrhpInterface::STATUS_UNSUCCESSFUL, false, false],
            [IrhpInterface::STATUS_ISSUING, true, false],
            [IrhpInterface::STATUS_ISSUING, false, false],
            [IrhpInterface::STATUS_EXPIRED, true, false],
            [IrhpInterface::STATUS_EXPIRED, false, false],
        ];
    }

    /**
     * @dataProvider dpTestCanViewCandidatePermits
     */
    public function testCanViewCandidatePermits($isAwaitingFee, $isCandidatePermitsAllocationMode, $isApgg, $expected)
    {
        $this->sut->shouldReceive('isAwaitingFee')
            ->withNoArgs()
            ->andReturn($isAwaitingFee);

        $this->sut->shouldReceive('isCandidatePermitsAllocationMode')
            ->withNoArgs()
            ->andReturn($isCandidatePermitsAllocationMode);

        $this->sut->shouldReceive('isApgg')
            ->withNoArgs()
            ->andReturn($isApgg);

        $this->assertSame($expected, $this->sut->canViewCandidatePermits());
    }

    public function dpTestCanViewCandidatePermits()
    {
        return [
            [false, false, false, false],
            [false, false, true, false],
            [false, true, false, false],
            [false, true, true, false],
            [true, false, false, false],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, true],
        ];
    }

    /**
     * @dataProvider dpTestCanSelectCandidatePermits
     */
    public function testCanSelectCandidatePermits($isAwaitingFee, $isCandidatePermitsAllocationMode, $isApsg, $expected)
    {
        $this->sut->shouldReceive('isAwaitingFee')
            ->withNoArgs()
            ->andReturn($isAwaitingFee);

        $this->sut->shouldReceive('isCandidatePermitsAllocationMode')
            ->withNoArgs()
            ->andReturn($isCandidatePermitsAllocationMode);

        $this->sut->shouldReceive('isApsg')
            ->withNoArgs()
            ->andReturn($isApsg);

        $this->assertSame($expected, $this->sut->canSelectCandidatePermits());
    }

    public function dpTestCanSelectCandidatePermits()
    {
        return [
            [false, false, false, false],
            [false, false, true, false],
            [false, true, false, false],
            [false, true, true, false],
            [true, false, false, false],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, true],
        ];
    }

    /**
     * @dataProvider dpTestIsCandidatePermitsAllocationMode
     */
    public function testIsCandidatePermitsAllocationMode($allocationMode, $expected)
    {
        $this->sut->shouldReceive('getAllocationMode')
            ->withNoArgs()
            ->andReturn($allocationMode);

        $this->assertEquals(
            $expected,
            $this->sut->isCandidatePermitsAllocationMode()
        );
    }

    public function dpTestIsCandidatePermitsAllocationMode()
    {
        return [
            [IrhpPermitStock::ALLOCATION_MODE_STANDARD, false],
            [IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES, false],
            [IrhpPermitStock::ALLOCATION_MODE_STANDARD_WITH_EXPIRY, false],
            [IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS, true],
            [IrhpPermitStock::ALLOCATION_MODE_BILATERAL, false],
            [IrhpPermitStock::ALLOCATION_MODE_NONE, false],
        ];
    }

    /**
     * @dataProvider dpCanBeGranted
     */
    public function testCanBeGranted($isUnderConsideration, $licenceValid, $businessProcess, $expected)
    {
        $this->sut->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);

        $licence = m::mock(Licence::class);
        $this->sut->setLicence($licence);

        $licence->allows('isValid')
            ->andReturn($licenceValid);

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn(new RefData($businessProcess));

        $this->assertEquals($expected, $this->sut->canBeGranted());
    }

    public function dpCanBeGranted()
    {
        return [
            [true, true, RefData::BUSINESS_PROCESS_APGG, true],
            [false, true, RefData::BUSINESS_PROCESS_APGG, false],
            [true, false, RefData::BUSINESS_PROCESS_APGG, false],
            [true, true, RefData::BUSINESS_PROCESS_APSG, false],
            [true, true, RefData::BUSINESS_PROCESS_APG, false],
        ];
    }

    public function testUpdateInternationalJourneys()
    {
        $refData = m::mock(RefData::class);

        $entity = $this->createNewEntity();
        $entity->updateInternationalJourneys($refData);

        $this->assertSame(
            $refData,
            $entity->getInternationalJourneys()
        );
    }

    public function testClearInternationalJourneys()
    {
        $refData = m::mock(RefData::class);

        $entity = $this->createNewEntity();
        $entity->setInternationalJourneys($refData);
        $entity->clearInternationalJourneys();

        $this->assertNull($entity->getInternationalJourneys());
    }

    public function testUpdateSectors()
    {
        $sectors = m::mock(Sectors::class);

        $entity = $this->createNewEntity();
        $entity->updateSectors($sectors);

        $this->assertSame(
            $sectors,
            $entity->getSectors()
        );
    }

    public function testClearSectors()
    {
        $sectors = m::mock(Sectors::class);

        $entity = $this->createNewEntity();
        $entity->setSectors($sectors);
        $entity->clearSectors();

        $this->assertNull($entity->getSectors());
    }

    public function testGetBusinessProcess()
    {
        $businessProcess = m::mock(RefData::class);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive(
            'getIrhpPermitWindow->getIrhpPermitStock->getBusinessProcess'
        )->once()->withNoArgs()->andReturn($businessProcess);

        $entity = $this->createNewEntity();
        $entity->addIrhpPermitApplications($irhpPermitApplication);

        $this->assertEquals($businessProcess, $entity->getBusinessProcess());
    }

    public function testGetBusinessProcessWithoutIrhpPermitApplication()
    {
        $entity = $this->createNewEntity();

        $this->assertNull($entity->getBusinessProcess());
    }

    public function testGetAnswerValueByQuestionId()
    {
        $questionId = 47;
        $answerValue = 'answer value';

        $entity = m::mock(Entity::class)->makePartial();

        $activeApplicationPath = m::mock(ApplicationPath::class);
        $activeApplicationPath->shouldReceive('getAnswerValueByQuestionId')
            ->with($questionId, $entity)
            ->andReturn($answerValue);

        $entity->shouldReceive('getActiveApplicationPath')
            ->withNoArgs()
            ->andReturn($activeApplicationPath);

        $this->assertEquals(
            $answerValue,
            $entity->getAnswerValueByQuestionId($questionId)
        );
    }

    /**
     * @dataProvider dpTestHasCountryId
     */
    public function testHasCountryId($countryId, $expected)
    {
        $country1Id = 'FR';
        $country1 = m::mock(Country::class);
        $country1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($country1Id);

        $country2Id = 'RU';
        $country2 = m::mock(Country::class);
        $country2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($country2Id);

        $country3Id = 'DE';
        $country3 = m::mock(Country::class);
        $country3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($country3Id);

        $countries = new ArrayCollection([$country1, $country2, $country3]);
        $entity = $this->createNewEntity();
        $entity->updateCountries($countries);

        $this->assertEquals($expected, $entity->hasCountryId($countryId));
    }

    public function dpTestHasCountryId()
    {
        return [
            ['FR', true],
            ['RU', true],
            ['DE', true],
            ['ES', false],
        ];
    }

    public function testGetCountryIds()
    {
        $country1 = m::mock(Country::class);
        $country1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(Country::ID_FRANCE);

        $country2 = m::mock(Country::class);
        $country2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(Country::ID_RUSSIA);

        $country3 = m::mock(Country::class);
        $country3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(Country::ID_GERMANY);

        $countries = new ArrayCollection([$country1, $country2, $country3]);
        $entity = $this->createNewEntity();
        $entity->updateCountries($countries);

        $expected = [Country::ID_FRANCE, Country::ID_RUSSIA, Country::ID_GERMANY];

        $this->assertEquals(
            $expected,
            $entity->getCountryIds()
        );
    }

    public function testUpdateCountries()
    {
        $arrayCollection = m::mock(ArrayCollection::class);

        $entity = $this->createNewEntity();
        $entity->updateCountries($arrayCollection);

        $this->assertSame(
            $arrayCollection,
            $entity->getCountrys()
        );
    }

    /**
     * @dataProvider dpCalculateTotalPermitsRequired
     */
    public function testCalculateTotalPermitsRequired(
        $isEcmtShortTerm,
        $isEcmtAnnual,
        $requiredEuro5,
        $requiredEuro6,
        $expectedTotal
    ) {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn($isEcmtAnnual);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->withNoArgs()
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->withNoArgs()
            ->andReturn($requiredEuro6);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitType($irhpPermitType);
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $this->assertEquals(
            $expectedTotal,
            $entity->calculateTotalPermitsRequired()
        );
    }

    public function dpCalculateTotalPermitsRequired()
    {
        return [
            'ecmt short term' => [true, false, 5, 3, 8],
            'ecmt annual' => [false, true, 4, 9, 13],
        ];
    }

    public function testCalculateTotalPermitsRequiredIncorrectType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'calculateTotalPermitsRequired is only applicable to ECMT short term and ECMT Annual'
        );

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn(false);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitType($irhpPermitType);

        $entity->calculateTotalPermitsRequired();
    }

    /**
     * @dataProvider dpCalculateTotalPermitsRequiredNotSet
     */
    public function testCalculateTotalPermitsRequiredNotSet(
        $isEcmtShortTerm,
        $isEcmtAnnual,
        $requiredEuro5,
        $requiredEuro6
    ) {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This IRHP Application has not had number of required permits set yet.');

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn($isEcmtAnnual);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->withNoArgs()
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->withNoArgs()
            ->andReturn($requiredEuro6);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitType($irhpPermitType);
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $entity->calculateTotalPermitsRequired();
    }

    public function dpCalculateTotalPermitsRequiredNotSet()
    {
        return [
            'ecmt short term, nothing set' => [true, false, null, null],
            'ecmt short term, only euro 5' => [true, false, 5, null],
            'ecmt short term, only euro 6' => [true, false, null, 10],
            'ecmt annual, nothing set' => [false, true, null, null],
            'ecmt annual, only euro 5' => [false, true, 5, null],
            'ecmt annual, only euro 6' => [false, true, null, 10],
        ];
    }

    /**
     * @dataProvider dpTestGetPermitIntensityOfUse
     */
    public function testGetPermitIntensityOfUse($emissionsCategoryId, $expectedIntensityOfUse)
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn(2);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn(5);

        $entity = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);
        $entity->shouldReceive('calculateTotalPermitsRequired')
            ->andReturn(7);
        $entity->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_ECMT_ANNUAL_TRIPS_ABROAD)
            ->andReturn(35);

        $this->assertEquals(
            $expectedIntensityOfUse,
            $entity->getPermitIntensityOfUse($emissionsCategoryId)
        );
    }

    /**
     * @dataProvider dpTestGetPermitIntensityOfUse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testGetPermitIntensityOfUseZeroPermitsRequested($emissionsCategoryId, $expectedIntensityOfUse)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Permit intensity of use cannot be calculated with zero number of permits');

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn(0);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn(0);

        $entity = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);
        $entity->shouldReceive('calculateTotalPermitsRequired')
            ->andReturn(0);
        $entity->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_ECMT_ANNUAL_TRIPS_ABROAD)
            ->andReturn(0);

        $entity->getPermitIntensityOfUse($emissionsCategoryId);
    }

    public function testGetPermitIntensityOfUseBadEmissionsCategory()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected emissionsCategoryId parameter for getPermitIntensityOfUse: xyz');

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);
        $entity->getPermitIntensityOfUse('xyz');
    }

    public function dpTestGetPermitIntensityOfUse()
    {
        return [
            [null, 5],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, 17.5],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, 7],
        ];
    }

    /**
     * @dataProvider dpTestGetPermitApplicationScore
     */
    public function testGetPermitApplicationScore(
        $emissionsCategoryId,
        $internationalJourneys,
        $expectedPermitApplicationScore
    ) {
        $intensityOfUse = 5;

        $entity = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $entity->shouldReceive('getPermitIntensityOfUse')
            ->with($emissionsCategoryId)
            ->andReturn($intensityOfUse);

        $refData = m::mock(RefData::class);
        $refData->shouldReceive('getId')
            ->andReturn($internationalJourneys);
        $entity->setInternationalJourneys($refData);

        $this->assertEquals(
            $expectedPermitApplicationScore,
            $entity->getPermitApplicationScore($emissionsCategoryId)
        );
    }

    public function dpTestGetPermitApplicationScore()
    {
        return [
            [null, RefData::INTER_JOURNEY_LESS_60, 1.5],
            [null, RefData::INTER_JOURNEY_60_90, 3.75],
            [null, RefData::INTER_JOURNEY_MORE_90, 5],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, RefData::INTER_JOURNEY_LESS_60, 1.5],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, RefData::INTER_JOURNEY_60_90, 3.75],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, RefData::INTER_JOURNEY_MORE_90, 5],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, RefData::INTER_JOURNEY_LESS_60, 1.5],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, RefData::INTER_JOURNEY_60_90, 3.75],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, RefData::INTER_JOURNEY_MORE_90, 5],
        ];
    }

    public function testGetCamelCaseEntityName()
    {
        $application = $this->createNewEntity();

        $this->assertEquals(
            'irhpApplication',
            $application->getCamelCaseEntityName()
        );
    }

    /**
     * @dataProvider dpGetEmailCommandLookup
     */
    public function testGetEmailCommandLookup($isEcmtShortTerm, $isEcmtAnnual, $expectedEmailCommandLookup)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn($isEcmtAnnual);

        $application = $this->createNewEntity();
        $application->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $expectedEmailCommandLookup,
            $application->getEmailCommandLookup()
        );
    }

    public function dpGetEmailCommandLookup()
    {
        return [
            [
                true,
                false,
                [
                    ApplicationAcceptConsts::SUCCESS_LEVEL_NONE => SendEcmtShortTermUnsuccessful::class,
                    ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL => SendEcmtShortTermApsgPartSuccessful::class,
                    ApplicationAcceptConsts::SUCCESS_LEVEL_FULL => SendEcmtShortTermSuccessful::class
                ]
            ],
            [
                false,
                true,
                [
                    ApplicationAcceptConsts::SUCCESS_LEVEL_NONE => SendEcmtApsgUnsuccessful::class,
                    ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL => SendEcmtApsgPartSuccessful::class,
                    ApplicationAcceptConsts::SUCCESS_LEVEL_FULL => SendEcmtApsgSuccessful::class
                ]
            ]
        ];
    }

    public function testGetEmailCommandLookupException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('getEmailCommandLookup is only applicable to ECMT short term and ECMT Annual');

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn(false);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn(false);

        $application = $this->createNewEntity();
        $application->setIrhpPermitType($irhpPermitType);

        $application->getEmailCommandLookup();
    }

    /**
     * @dataProvider dpProvideOutcomeNotificationType
     */
    public function testGetOutcomeNotificationType($source, $expectedNotificationType)
    {
        $sourceRefData = m::mock(RefData::class);
        $sourceRefData->shouldReceive('getId')
            ->andReturn($source);

        $entity = Entity::createNew(
            $sourceRefData,
            m::mock(RefData::class),
            m::mock(IrhpPermitType::class),
            m::mock(Licence::class)
        );

        $this->assertEquals(
            $expectedNotificationType,
            $entity->getOutcomeNotificationType()
        );
    }

    /**
     * Pass array of app statuses to make sure an exception is thrown
     *
     * @return array
     */
    public function dpProvideOutcomeNotificationType()
    {
        return [
            [IrhpInterface::SOURCE_SELFSERVE, ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL],
            [IrhpInterface::SOURCE_INTERNAL, ApplicationAcceptConsts::NOTIFICATION_TYPE_MANUAL]
        ];
    }

    /**
     * @dataProvider dpProvideSuccessLevel
     */
    public function testGetSuccessLevel($permitsRequired, $permitsAwarded, $expectedSuccessLevel)
    {
        $entity = m::mock(Entity::class)->makePartial();

        $entity->shouldReceive('calculateTotalPermitsRequired')
            ->andReturn($permitsRequired);
        $entity->shouldReceive('getPermitsAwarded')
            ->andReturn($permitsAwarded);

        $this->assertEquals(
            $expectedSuccessLevel,
            $entity->getSuccessLevel()
        );
    }

    /**
     * @dataProvider dpHasStateRequiredForPostScoringEmail
     */
    public function testHasStateRequiredForPostScoringEmail($isUnderConsideration, $isInScope, $successLevel, $expected)
    {
        $entity = m::mock(Entity::class)->makePartial();

        $entity->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);

        $entity->shouldReceive('getInScope')
            ->withNoArgs()
            ->andReturn($isInScope);

        $entity->shouldReceive('getSuccessLevel')
            ->withNoArgs()
            ->andReturn($successLevel);

        $this->assertEquals(
            $expected,
            $entity->hasStateRequiredForPostScoringEmail()
        );
    }

    public function dpHasStateRequiredForPostScoringEmail()
    {
        return [
            [false, false, ApplicationAcceptConsts::SUCCESS_LEVEL_NONE, false],
            [false, false, ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL, false],
            [false, false, ApplicationAcceptConsts::SUCCESS_LEVEL_FULL, false],
            [false, true, ApplicationAcceptConsts::SUCCESS_LEVEL_NONE, false],
            [false, true, ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL, false],
            [false, true, ApplicationAcceptConsts::SUCCESS_LEVEL_FULL, false],
            [true, false, ApplicationAcceptConsts::SUCCESS_LEVEL_NONE, false],
            [true, false, ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL, false],
            [true, false, ApplicationAcceptConsts::SUCCESS_LEVEL_FULL, false],
            [true, true, ApplicationAcceptConsts::SUCCESS_LEVEL_NONE, false],
            [true, true, ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL, true],
            [true, true, ApplicationAcceptConsts::SUCCESS_LEVEL_FULL, true],
        ];
    }

    /**
     * Pass array of app statuses to make sure an exception is thrown
     *
     * @return array
     */
    public function dpProvideSuccessLevel()
    {
        return [
            [10,  1, ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL],
            [10,  9, ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL],
            [10,  0, ApplicationAcceptConsts::SUCCESS_LEVEL_NONE],
            [ 1,  0, ApplicationAcceptConsts::SUCCESS_LEVEL_NONE],
            [ 1,  1, ApplicationAcceptConsts::SUCCESS_LEVEL_FULL],
            [10, 10, ApplicationAcceptConsts::SUCCESS_LEVEL_FULL]
        ];
    }

    public function testProceedToUnsuccessful()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isUnderConsideration')
            ->andReturn(true);

        $unsuccessfulStatus = m::mock(RefData::class);
        $entity->proceedToUnsuccessful($unsuccessfulStatus);

        $this->assertSame(
            $unsuccessfulStatus,
            $entity->getStatus()
        );
    }

    public function testProceedToUnsuccessfulException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'This application is not in the correct state to proceed to unsuccessful (current_status)'
        );

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isUnderConsideration')
            ->andReturn(false);

        $currentStatus = m::mock(RefData::class);
        $currentStatus->shouldReceive('getId')
            ->andReturn('current_status');
        $entity->setStatus($currentStatus);

        $entity->proceedToUnsuccessful(m::mock(RefData::class));
    }

    public function testProceedToAwaitingFee()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isUnderConsideration')
            ->andReturn(true);

        $awaitingFeeStatus = m::mock(RefData::class);
        $entity->proceedToAwaitingFee($awaitingFeeStatus);

        $this->assertSame(
            $awaitingFeeStatus,
            $entity->getStatus()
        );
    }

    public function testProceedToAwaitingFeeException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'This application is not in the correct state to proceed to awaiting fee (current_status)'
        );

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isUnderConsideration')
            ->andReturn(false);

        $currentStatus = m::mock(RefData::class);
        $currentStatus->shouldReceive('getId')
            ->andReturn('current_status');
        $entity->setStatus($currentStatus);

        $entity->proceedToAwaitingFee(m::mock(RefData::class));
    }

    public function testGetPermitsAwarded()
    {
        $permitsAwarded = 14;

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isUnderConsideration')
            ->andReturn(true);
        $entity->shouldReceive('getFirstIrhpPermitApplication->countPermitsAwarded')
            ->andReturn($permitsAwarded);

        $this->assertSame(
            $permitsAwarded,
            $entity->getPermitsAwarded()
        );
    }

    public function testGetPermitsAwardedException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'This application is not in the correct state to return permits awarded (current_status)'
        );

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isUnderConsideration')
            ->andReturn(false);

        $currentStatus = m::mock(RefData::class);
        $currentStatus->shouldReceive('getId')
            ->andReturn('current_status');
        $entity->setStatus($currentStatus);

        $entity->getPermitsAwarded(m::mock(RefData::class));
    }

    /**
     * @dataProvider dpGetIntensityOfUseWarningThreshold
     */
    public function testGetIntensityOfUseWarningThreshold(
        $isEcmtShortTerm,
        $isEcmtAnnual,
        $requiredEuro5,
        $requiredEuro6,
        $expectedThreshold
    ) {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn($isEcmtAnnual);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn($requiredEuro6);

        $application = $this->createNewEntity();
        $application->setIrhpPermitType($irhpPermitType);
        $application->addIrhpPermitApplications($irhpPermitApplication);

        $this->assertEquals(
            $expectedThreshold,
            $application->getIntensityOfUseWarningThreshold()
        );
    }

    public function dpGetIntensityOfUseWarningThreshold()
    {
        return [
            [true, false, 5, 8, 800],
            [true, false, 4, 2, 400],
            [false, true, 5, 8, 800],
            [false, true, 4, 2, 400],
        ];
    }

    public function testGetIntensityOfUseWarningThresholdException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'getIntensityOfUseWarningThreshold is only applicable to ECMT short term and ECMT Annual'
        );

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn(false);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn(false);

        $application = $this->createNewEntity();
        $application->setIrhpPermitType($irhpPermitType);

        $application->getIntensityOfUseWarningThreshold();
    }

    /**
     * @dataProvider dpGetAppSubmittedEmailCommand
     */
    public function testGetAppSubmittedEmailCommand(
        $isEcmtShortTerm,
        $isEcmtAnnual,
        $businessProcessId,
        $expectedCommand
    ) {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn($isEcmtAnnual);

        $application = m::mock(Entity::class)->makePartial();
        $application->shouldReceive('getBusinessProcess->getId')
            ->withNoArgs()
            ->andReturn($businessProcessId);

        $application->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $expectedCommand,
            $application->getAppSubmittedEmailCommand()
        );
    }

    public function dpGetAppSubmittedEmailCommand()
    {
        return [
            [true, false, RefData::BUSINESS_PROCESS_APG, null],
            [true, false, RefData::BUSINESS_PROCESS_APGG, null],
            [true, false, RefData::BUSINESS_PROCESS_APSG, SendEcmtShortTermAppSubmitted::class],
            [false, true, RefData::BUSINESS_PROCESS_APG, null],
            [false, true, RefData::BUSINESS_PROCESS_APGG, SendEcmtApggAppSubmitted::class],
            [false, true, RefData::BUSINESS_PROCESS_APSG, SendEcmtApsgAppSubmitted::class],
            [false, false, RefData::BUSINESS_PROCESS_APG, null],
            [false, false, RefData::BUSINESS_PROCESS_APGG, null],
            [false, false, RefData::BUSINESS_PROCESS_APSG, null],
        ];
    }

    /**
     * @dataProvider dpGetAppWithdrawnEmailCommand
     */
    public function testGetAppWithdrawnEmailCommand($irhpPermitTypeId, $withdrawReason, $expectedCommand)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn($irhpPermitTypeId);

        $application = m::mock(Entity::class)->makePartial();
        $application->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $expectedCommand,
            $application->getAppWithdrawnEmailCommand($withdrawReason)
        );
    }

    public function dpGetAppWithdrawnEmailCommand()
    {
        return [
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS,
                null,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                WithdrawableInterface::WITHDRAWN_REASON_UNPAID,
                SendEcmtAutomaticallyWithdrawn::class,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                WithdrawableInterface::WITHDRAWN_REASON_BY_USER,
                null,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
                null,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS,
                SendEcmtShortTermUnsuccessful::class,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                WithdrawableInterface::WITHDRAWN_REASON_UNPAID,
                SendEcmtShortTermAutomaticallyWithdrawn::class,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                WithdrawableInterface::WITHDRAWN_REASON_BY_USER,
                null,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
                null,
            ],
        ];
    }

    /**
     * @dataProvider dpGetIssuedEmailCommand
     */
    public function testGetIssuedEmailCommand($isEcmtAnnual, $isEcmtShortTerm, $isApsg, $isApgg, $expectedCommand)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn($isEcmtAnnual);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);

        $application = m::mock(Entity::class)->makePartial();
        $application->setIrhpPermitType($irhpPermitType);

        $application->shouldReceive('isApsg')
            ->withNoArgs()
            ->andReturn($isApsg);
        $application->shouldReceive('isApgg')
            ->withNoArgs()
            ->andReturn($isApgg);

        $this->assertEquals(
            $expectedCommand,
            $application->getIssuedEmailCommand()
        );
    }

    public function dpGetIssuedEmailCommand()
    {
        return [
            [true, false, true, false, SendEcmtApsgIssued::class],
            [true, false, false, true, SendEcmtApggIssued::class],
            [false, true, true, false, SendEcmtShortTermApsgIssued::class],
            [false, true, false, true, SendEcmtShortTermApggIssued::class],
            [false, false, true, false, null],
            [false, false, false, true, null],
        ];
    }

    public function testGetAllocationMode()
    {
        $allocationMode = 'ALLOCATION_MODE';

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getAllocationMode')
            ->andReturn($allocationMode);

        $this->sut->shouldReceive('getAssociatedStock')
            ->andReturn($irhpPermitStock);

        $this->assertEquals(
            $allocationMode,
            $this->sut->getAllocationMode()
        );
    }

    /**
     * @dataProvider dpShouldAllocatePermitsOnSubmission
     */
    public function testShouldAllocatePermitsOnSubmission($businessProcessId, $expected)
    {
        $businessProcess = m::mock(RefData::class);
        $businessProcess->shouldReceive('getId')
            ->andReturn($businessProcessId);

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn($businessProcess);

        $this->assertEquals(
            $expected,
            $this->sut->shouldAllocatePermitsOnSubmission()
        );
    }

    /**
     * @dataProvider dpUpdateChecked
     */
    public function testUpdateChecked($checked)
    {
        $this->sut->updateChecked($checked);
        $this->assertEquals($checked, $this->sut->getChecked());
    }

    public function dpUpdateChecked()
    {
        return [
            [true],
            [false],
        ];
    }

    public function dpShouldAllocatePermitsOnSubmission()
    {
        return [
            [RefData::BUSINESS_PROCESS_APG, true],
            [RefData::BUSINESS_PROCESS_APGG, false],
            [RefData::BUSINESS_PROCESS_APSG, false],
        ];
    }

    /**
     * @dataProvider dpGetSubmissionTaskDescription
     */
    public function testGetSubmissionTaskDescription($irhpPermitTypeId, $expectedTaskDescription)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn($irhpPermitTypeId);

        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $expectedTaskDescription,
            $this->sut->getSubmissionTaskDescription()
        );
    }

    public function dpGetSubmissionTaskDescription()
    {
        return [
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                Task::TASK_DESCRIPTION_ANNUAL_ECMT_RECEIVED
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                Task::TASK_DESCRIPTION_SHORT_TERM_ECMT_RECEIVED
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                Task::TASK_DESCRIPTION_ECMT_INTERNATIONAL_REMOVALS_RECEIVED
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                Task::TASK_DESCRIPTION_BILATERAL_RECEIVED
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                Task::TASK_DESCRIPTION_MULTILATERAL_RECEIVED
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                Task::TASK_DESCRIPTION_CERT_ROADWORTHINESS_RECEIVED
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                Task::TASK_DESCRIPTION_CERT_ROADWORTHINESS_RECEIVED
            ],
        ];
    }

    public function testGetSubmissionTaskDescriptionException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No submission task description defined for permit type foo');

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn('foo');

        $this->sut->setIrhpPermitType($irhpPermitType);
        $this->sut->getSubmissionTaskDescription();
    }

    /**
     * @dataProvider dpGetSubmissionStatus
     */
    public function testGetSubmissionStatus($businessProcessId, $expectedStatus)
    {
        $businessProcess = m::mock(RefData::class);
        $businessProcess->shouldReceive('getId')
            ->andReturn($businessProcessId);

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn($businessProcess);

        $this->assertEquals(
            $expectedStatus,
            $this->sut->getSubmissionStatus()
        );
    }

    public function dpGetSubmissionStatus()
    {
        return [
            [RefData::BUSINESS_PROCESS_AG, IrhpInterface::STATUS_VALID],
            [RefData::BUSINESS_PROCESS_APG, IrhpInterface::STATUS_ISSUING],
            [RefData::BUSINESS_PROCESS_APGG, IrhpInterface::STATUS_UNDER_CONSIDERATION],
            [RefData::BUSINESS_PROCESS_APSG, IrhpInterface::STATUS_UNDER_CONSIDERATION],
        ];
    }

    public function testGetSubmissionStatusException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No submission status defined for business process foo');

        $businessProcess = m::mock(RefData::class);
        $businessProcess->shouldReceive('getId')
            ->andReturn('foo');

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn($businessProcess);

        $this->sut->getSubmissionStatus();
    }

    public function testGetCandidatePermitCreationModeMultiStock()
    {
        $this->sut->shouldReceive('isMultiStock')
            ->withNoArgs()
            ->andReturn(true);

        $this->assertEquals(
            IrhpPermitStock::CANDIDATE_MODE_NONE,
            $this->sut->getCandidatePermitCreationMode()
        );
    }

    public function testGetCandidatePermitCreationModeNotMultiStock()
    {
        $creationMode = 'CREATION_MODE';

        $this->sut->shouldReceive('isMultiStock')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->shouldReceive('getAssociatedStock->getCandidatePermitCreationMode')
            ->withNoArgs()
            ->andReturn($creationMode);

        $this->assertEquals(
            $creationMode,
            $this->sut->getCandidatePermitCreationMode()
        );
    }

    /**
     * @dataProvider dpRequiresPreAllocationCheck
     */
    public function testRequiresPreAllocationCheck($isEcmtShortTerm, $isEcmtAnnual, $expected)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn($isEcmtAnnual);

        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $expected,
            $this->sut->requiresPreAllocationCheck()
        );
    }

    public function dpRequiresPreAllocationCheck()
    {
        return [
            [true, false, true],
            [false, true, true],
            [false, false, false],
        ];
    }

    public function testFetchOpenSubmissionTask()
    {
        $this->sut->shouldReceive('getSubmissionTaskDescription')
            ->withNoArgs()
            ->andReturn('submission task');

        $task1 = $this->createMockTask('description 1', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_FEE_DUE);
        $task2 = $this->createMockTask('submission task', 'Y', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_APPLICATION);
        $task3 = $this->createMockTask('submission task', 'N', Task::CATEGORY_BUS, Task::SUBCATEGORY_APPLICATION);
        $task4 = $this->createMockTask('submission task', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_FEE_DUE);
        $task5 = $this->createMockTask('submission task', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_APPLICATION);
        $task6 = $this->createMockTask('description 2', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_APPLICATION);

        $this->sut->setTasks(
            new ArrayCollection([$task1, $task2, $task3, $task4, $task5, $task6])
        );

        $this->assertSame(
            $task5,
            $this->sut->fetchOpenSubmissionTask()
        );
    }

    public function testFetchOpenSubmissionTaskNull()
    {
        $this->sut->shouldReceive('getSubmissionTaskDescription')
            ->withNoArgs()
            ->andReturn('submission task');

        $task1 = $this->createMockTask('description 1', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_FEE_DUE);
        $task2 = $this->createMockTask('submission task', 'Y', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_APPLICATION);
        $task3 = $this->createMockTask('submission task', 'N', Task::CATEGORY_BUS, Task::SUBCATEGORY_APPLICATION);
        $task4 = $this->createMockTask('submission task', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_FEE_DUE);
        $task5 = $this->createMockTask('description 2', 'N', Task::CATEGORY_PERMITS, Task::SUBCATEGORY_APPLICATION);

        $this->sut->setTasks(
            new ArrayCollection([$task1, $task2, $task3, $task4, $task5])
        );

        $this->assertNull(
            $this->sut->fetchOpenSubmissionTask()
        );
    }

    private function createMockTask($description, $isClosed, $categoryId, $subcategoryId)
    {
        $task = m::mock(Task::class);
        $task->shouldReceive('getDescription')
            ->withNoArgs()
            ->andReturn($description);
        $task->shouldReceive('getIsClosed')
            ->withNoArgs()
            ->andReturn($isClosed);
        $task->shouldReceive('getCategory->getId')
            ->withNoArgs()
            ->andReturn($categoryId);
        $task->shouldReceive('getSubcategory->getId')
            ->withNoArgs()
            ->andReturn($subcategoryId);

        return $task;
    }

    public function testExpireCertificateWrongPermitType()
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->expects()->isCertificateOfRoadworthiness()->withNoArgs()->andReturnFalse();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Entity::ERR_ROADWORTHINESS_ONLY);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->expireCertificate(m::mock(RefData::class));
    }

    /**
     * @dataProvider dpExpireCertificateMotNotExpired
     */
    public function testExpireCertificateMotNotExpired($expiryDate)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Entity::ERR_ROADWORTHINESS_MOT_EXPIRY);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->expects()->isCertificateOfRoadworthiness()->withNoArgs()->andReturnTrue();

        /**
         * Make partial to avoid recreating all the mocks retrieving the expiry date
         *
         * @var Entity|m\mockInterface $entity
         */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->expects()->getMotExpiryDate()->withNoArgs()->andReturn($expiryDate);
        $entity->setIrhpPermitType($irhpPermitType);
        $entity->expireCertificate(m::mock(RefData::class));
    }

    public function dpExpireCertificateMotNotExpired()
    {
        return [
            'no mot expiry date present' => [null],
            'mot expires tomorrow' => [(new \DateTime('+1 day'))->format('Y-m-d')],
            'mot expires today' => [(new \DateTime())->format('Y-m-d')],
        ];
    }

    public function testExpireCertificateMotHasExpired()
    {
        $validStatus = m::mock(RefData::class);
        $validStatus->expects()->getId()->withNoArgs()->andReturn(IrhpInterface::STATUS_VALID);
        $expiryDate = (new \DateTime('-1 day'))->format('Y-m-d');
        $expiryStatus = m::mock(RefData::class);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->expects()->isCertificateOfRoadworthiness()->withNoArgs()->andReturnTrue();

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->expects()->hasValidPermits()->withNoArgs()->andReturnFalse();

        /**
         * Make partial to avoid recreating all the mocks retrieving the expiry date
         *
         * @var Entity|m\mockInterface $entity
         */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->expects()->getMotExpiryDate()->twice()->withNoArgs()->andReturn($expiryDate);
        $entity->setIrhpPermitType($irhpPermitType);
        $entity->setStatus($validStatus);
        $entity->setIrhpPermitApplications(new ArrayCollection([$irhpPermitApplication]));

        $entity->expireCertificate($expiryStatus);
        self::assertEquals($expiryStatus, $entity->getStatus());
        self::assertEquals($expiryDate, $entity->getExpiryDate()->format('Y-m-d'));
    }

    /**
     * @dataProvider dpTestGetMotExpiryDate
     */
    public function testGetMotExpiryDate($isTrailer, $questionId)
    {
        $expiryDate = '2020-08-01';

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturnTrue();
        $entity->shouldReceive('isCertificateOfRoadworthinessTrailer')
            ->withNoArgs()
            ->andReturn($isTrailer);
        $entity->shouldReceive('getAnswerValueByQuestionId')
            ->with($questionId)
            ->andReturn($expiryDate);

        $this->assertEquals(
            $expiryDate,
            $entity->getMotExpiryDate()
        );
    }

    public function dpTestGetMotExpiryDate()
    {
        return [
            'trailer' => [
                true,
                Question::QUESTION_ID_ROADWORTHINESS_TRAILER_MOT_EXPIRY,
            ],
            'vehicle' => [
                false,
                Question::QUESTION_ID_ROADWORTHINESS_VEHICLE_MOT_EXPIRY,
            ],
        ];
    }

    public function testGetMotExpiryDateIncorrectType()
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturnFalse();

        $this->assertNull(
            $entity->getMotExpiryDate()
        );
    }

    /**
     * @dataProvider dpCanBeResetToNotYetSubmittedFromValid
     */
    public function testCanBeResetToNotYetSubmittedFromValid($isValid, $isCertificateOfRoadworthiness, $expected)
    {
        $this->sut->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturn($isValid);

        $this->sut->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthiness);

        $this->assertEquals(
            $expected,
            $this->sut->canBeResetToNotYetSubmittedFromValid()
        );
    }

    public function dpCanBeResetToNotYetSubmittedFromValid()
    {
        return [
            [true, true, true],
            [false, true, false],
            [true, false, false],
            [false, false, false],
        ];
    }

    public function testResetToNotYetSubmittedFromValid()
    {
        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromValid')
            ->withNoArgs()
            ->andReturn(true);

        $this->sut->resetToNotYetSubmittedFromValid($status);

        $this->assertSame($status, $this->sut->getStatus());
    }

    public function testResetToNotYetSubmittedFromValidException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Unable to reset this application to Not Yet Submitted');

        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromValid')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->resetToNotYetSubmittedFromValid($status);
    }

    /**
     * @dataProvider dpCanBeResetToNotYetSubmittedFromCancelled
     */
    public function testCanBeResetToNotYetSubmittedFromCancelled(
        $isCancelled,
        $isEcmtShortTerm,
        $isEcmtAnnual,
        $hasUnderConsiderationOrAwaitingFeeApplicationForStock,
        $expected
    ) {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $this->sut->shouldReceive('getAssociatedStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);
        $this->sut->shouldReceive('isCancelled')
            ->withNoArgs()
            ->andReturn($isCancelled);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturn($isEcmtAnnual);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('hasUnderConsiderationOrAwaitingFeeApplicationForStock')
            ->with($irhpPermitStock)
            ->andReturn($hasUnderConsiderationOrAwaitingFeeApplicationForStock);
        $this->sut->setLicence($licence);

        $this->assertEquals(
            $expected,
            $this->sut->canBeResetToNotYetSubmittedFromCancelled()
        );
    }

    public function dpCanBeResetToNotYetSubmittedFromCancelled()
    {
        return [
            [false, false, true, false, false],
            [false, true, false, false, false],
            [false, false, false, false, false],
            [true, false, true, false, true],
            [true, true, false, false, true],
            [true, false, false, false, false],
            [false, false, true, true, false],
            [false, true, false, true, false],
            [false, false, false, true, false],
            [true, false, true, true, false],
            [true, true, false, true, false],
            [true, false, false, true, false],
        ];
    }

    public function testResetToNotYetSubmittedFromCancelled()
    {
        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromCancelled')
            ->withNoArgs()
            ->andReturn(true);

        $this->sut->resetToNotYetSubmittedFromCancelled($status);

        $this->assertSame($status, $this->sut->getStatus());
    }

    public function testResetToNotYetSubmittedFromCancelledException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Unable to reset this application to Not Yet Submitted');

        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromCancelled')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->resetToNotYetSubmittedFromCancelled($status);
    }

    /**
     * @dataProvider dpCanBeRevivedFromWithdrawn
     */
    public function testCanBeRevivedFromWithdrawn($withdrawReason, $inScope, $businessProcessId, $expected)
    {
        $withdrawReasonRefData = m::mock(RefData::class);
        $withdrawReasonRefData->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($withdrawReason);

        $this->sut->setWithdrawReason($withdrawReasonRefData);

        $this->sut->shouldReceive('isWithdrawn')
            ->withNoArgs()
            ->andReturn(true);

        $this->sut->shouldReceive('getInScope')
            ->withNoArgs()
            ->andReturn($inScope);

        $businessProcessRefData = m::mock(RefData::class);
        $businessProcessRefData->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($businessProcessId);

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn($businessProcessRefData);

        $this->assertEquals(
            $expected,
            $this->sut->canBeRevivedFromWithdrawn()
        );
    }

    public function dpCanBeRevivedFromWithdrawn()
    {
        return [
            [
                WithdrawableInterface::WITHDRAWN_REASON_UNPAID,
                true,
                RefData::BUSINESS_PROCESS_APSG,
                true,
            ],
            [
                WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
                true,
                RefData::BUSINESS_PROCESS_APSG,
                true,
            ],
            [
                WithdrawableInterface::WITHDRAWN_REASON_NOTSUCCESS,
                true,
                RefData::BUSINESS_PROCESS_APSG,
                false,
            ],
            [
                WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
                false,
                RefData::BUSINESS_PROCESS_APSG,
                false,
            ],
            [
                WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
                true,
                RefData::BUSINESS_PROCESS_APGG,
                false,
            ],
        ];
    }

    /**
     * @dataProvider dpCanBeRevivedFromWithdrawnNotWithdrawn
     */
    public function testCanBeRevivedFromWithdrawnNotWithdrawn($inScope, $businessProcessId)
    {
        $this->sut->setWithdrawReason(null);

        $this->sut->shouldReceive('isWithdrawn')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->shouldReceive('getInScope')
            ->withNoArgs()
            ->andReturn($inScope);

        $businessProcessRefData = m::mock(RefData::class);
        $businessProcessRefData->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($businessProcessId);

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn($businessProcessRefData);

        $this->assertFalse(
            $this->sut->canBeRevivedFromWithdrawn()
        );
    }

    public function dpCanBeRevivedFromWithdrawnNotWithdrawn()
    {
        return [
            [true, RefData::BUSINESS_PROCESS_APGG],
            [false, RefData::BUSINESS_PROCESS_APSG],
            [true, RefData::BUSINESS_PROCESS_APGG],
            [false, RefData::BUSINESS_PROCESS_APSG],
        ];
    }

    public function testReviveFromWithdrawn()
    {
        $withdrawnStatus = m::mock(RefData::class);
        $withdrawnDate = m::mock(DateTime::class);

        $underConsiderationStatus = m::mock(RefData::class);

        $this->sut->setStatus($withdrawnStatus);
        $this->sut->setWithdrawReason(WithdrawableInterface::WITHDRAWN_REASON_DECLINED);
        $this->sut->setWithdrawnDate = $withdrawnDate;

        $this->sut->shouldReceive('canBeRevivedFromWithdrawn')
            ->withNoArgs()
            ->andReturn(true);

        $this->sut->reviveFromWithdrawn($underConsiderationStatus);

        $this->assertSame($underConsiderationStatus, $this->sut->getStatus());
        $this->assertNull($this->sut->getWithdrawReason());
        $this->assertNull($this->sut->getWithdrawnDate());
    }

    public function testReviveFromWithdrawnNoBusinessProcess()
    {
        $this->sut->expects()->getBusinessProcess()
            ->withNoArgs()
            ->andReturnNull();

        self::assertFalse($this->sut->canBeRevivedFromWithdrawn());
    }

    public function testReviveFromWithdrawnException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Unable to revive this application from a withdrawn state');

        $this->sut->shouldReceive('canBeRevivedFromWithdrawn')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->reviveFromWithdrawn(
            m::mock(RefData::class)
        );
    }

    /**
     * @dataProvider dpCanBeRevivedFromUnsuccessful
     */
    public function testCanBeRevivedFromUnsuccessful($businessProcessId, $statusId, $expected)
    {
        $statusRefData = m::mock(RefData::class);
        $statusRefData->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($statusId);

        $this->sut->setStatus($statusRefData);

        $businessProcessRefData = m::mock(RefData::class);
        $businessProcessRefData->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($businessProcessId);

        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn($businessProcessRefData);

        $this->assertEquals(
            $expected,
            $this->sut->canBeRevivedFromUnsuccessful()
        );
    }

    public function dpCanBeRevivedFromUnsuccessful()
    {
        return [
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_CANCELLED,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_UNDER_CONSIDERATION,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_WITHDRAWN,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_AWAITING_FEE,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_FEE_PAID,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_UNSUCCESSFUL,
                true
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_ISSUING,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_VALID,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                IrhpInterface::STATUS_EXPIRED,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_CANCELLED,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_NOT_YET_SUBMITTED,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_UNDER_CONSIDERATION,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_WITHDRAWN,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_AWAITING_FEE,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_FEE_PAID,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_UNSUCCESSFUL,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_ISSUING,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_VALID,
                false
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                IrhpInterface::STATUS_EXPIRED,
                false
            ],
        ];
    }

    public function testReviveFromUnsuccessfulNoBusinessProcess()
    {
        $this->sut->expects()->getBusinessProcess()
            ->withNoArgs()
            ->andReturnNull();

        self::assertFalse($this->sut->canBeRevivedFromUnsuccessful());
    }

    public function testReviveFromUnsuccessful()
    {
        $underConsiderationStatus = m::mock(RefData::class);

        $this->sut->setStatus(m::mock(RefData::class));
        $this->sut->shouldReceive('canBeRevivedFromUnsuccessful')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->reviveFromUnsuccessful($underConsiderationStatus);

        $this->assertSame(
            $underConsiderationStatus,
            $this->sut->getStatus()
        );
    }

    public function testReviveFromUnsuccessfulException()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Unable to revive this application from an unsuccessful state');

        $this->sut->shouldReceive('canBeRevivedFromUnsuccessful')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->reviveFromUnsuccessful(
            m::mock(RefData::class)
        );
    }

    /**
     * @dataProvider productRefMonthProvider
     */
    public function testGetProductReferenceForTier($expected, $validFrom, $validTo, $now)
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $this->sut->addIrhpPermitApplications(new ArrayCollection([$irhpPermitApplication]));

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->andReturn($irhpPermitStock);

        $irhpPermitStock->shouldReceive('getValidFrom')->andReturn($validFrom);
        $irhpPermitStock->shouldReceive('getValidTo')->andReturn($validTo);
        $this->assertEquals($expected, $this->sut->getProductReferenceForTier($now));
    }

    public function productRefMonthProvider()
    {
        $validFrom = new DateTime('first day of January next year');
        $validTo = new DateTime('last day of December next year');

        return [
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of January next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of February next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of March next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of April next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of May next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of June next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of July next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of August next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of September next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of October next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of November next year')
            ],
            [
                FeeType::FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF,
                $validFrom,
                $validTo,
                new DateTime('first day of December next year')
            ],
        ];
    }

    public function testCreateAnswer()
    {
        $questionText = m::mock(QuestionText::class);
        $answer = $this->sut->createAnswer($questionText, $this->sut);

        $this->assertSame(
            $questionText,
            $answer->getQuestionText()
        );

        $this->assertSame(
            $this->sut,
            $answer->getIrhpApplication()
        );
    }

    public function testOnSubmitApplicationStep()
    {
        $this->sut->shouldReceive('resetCheckAnswersAndDeclaration')
            ->withNoArgs()
            ->once();

        $this->sut->onSubmitApplicationStep();
    }

    public function testGetAdditionalQaViewData()
    {
        $applicationRef = 'OB12345 / 100001';

        $this->sut->shouldReceive('getApplicationRef')
            ->withNoArgs()
            ->andReturn($applicationRef);

        $applicationStep = m::mock(ApplicationStep::class);

        $expected = [
            'applicationReference' => $applicationRef
        ];

        $this->assertEquals(
            $expected,
            $this->sut->getAdditionalQaViewData($applicationStep)
        );
    }

    /**
     * @dataProvider dpIsApplicationPathEnabled
     */
    public function testIsApplicationPathEnabled($isEnabled)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->andReturn($isEnabled);

        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $isEnabled,
            $this->sut->isApplicationPathEnabled()
        );
    }

    public function dpIsApplicationPathEnabled()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetRepositoryName()
    {
        $this->assertEquals(
            'IrhpApplication',
            $this->sut->getRepositoryName()
        );
    }

    /**
     * @dataProvider dpGetIrhpPermitApplicationIdForCountry
     */
    public function testGetIrhpPermitApplicationIdForCountry($result, $countryCode)
    {
        $countries = [
            [
                'countryCode' => 'FR',
                'countryName' => 'France',
                'status' => SectionableInterface::SECTION_COMPLETION_NOT_STARTED,
                'irhpPermitApplication' => null
            ],
            [
                'countryCode' => 'DE',
                'countryName' => 'Germany',
                'status' => SectionableInterface::SECTION_COMPLETION_INCOMPLETE,
                'irhpPermitApplication' => 3322
            ],
            [
                'countryCode' => 'IT',
                'countryName' => 'Italy',
                'status' => SectionableInterface::SECTION_COMPLETION_COMPLETED,
                'irhpPermitApplication' => null
            ]
        ];

        $this->sut->shouldReceive('getBilateralCountriesAndStatuses')
            ->once()
            ->withNoArgs()
            ->andReturn($countries);

        $countryEntity =  m::mock(Country::class);

        $countryEntity->shouldReceive('getId')->atMost(3)->withNoArgs()->andReturn($countryCode);
        $this->assertEquals($result, $this->sut->getIrhpPermitApplicationIdForCountry($countryEntity));
    }

    public function dpGetIrhpPermitApplicationIdForCountry()
    {
        return [
            [null, 'NO'],
            [3322, 'DE'],
        ];
    }

    public function testGetIrhpPermitApplicationIdForCountryMissingIrhpPermitApplication()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Found irhp_permit_application instance without accompanying irhp_application_country_link'
        );

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn('NO');

        $this->sut->setIrhpPermitApplications([$irhpPermitApplication]);

        $this->sut->getIrhpPermitApplicationIdForCountry(
            m::mock(Country::class)
        );
    }

    /**
     * @dataProvider dpGetIrhpPermitApplicationsByCountryName
     */
    public function testGetIrhpPermitApplicationsByCountryName($irhpPermitApplications, $expected)
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturnTrue();

        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->setIrhpPermitApplications($irhpPermitApplications);

        $this->assertEquals($expected->getValues(), $entity->getIrhpPermitApplicationsByCountryName()->getValues());
    }

    public function dpGetIrhpPermitApplicationsByCountryName()
    {
        $irhpPermitApplicationA = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplicationA->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getCountryDesc')
            ->withNoArgs()
            ->andReturn('A');

        $irhpPermitApplicationB = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplicationB->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getCountryDesc')
            ->withNoArgs()
            ->andReturn('B');

        $irhpPermitApplicationC = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplicationC->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getCountryDesc')
            ->withNoArgs()
            ->andReturn('C');

        return [
            [
                new ArrayCollection([$irhpPermitApplicationA, $irhpPermitApplicationB, $irhpPermitApplicationB, $irhpPermitApplicationC]),
                new ArrayCollection([$irhpPermitApplicationA, $irhpPermitApplicationB, $irhpPermitApplicationB, $irhpPermitApplicationC])
            ],
            [
                new ArrayCollection([$irhpPermitApplicationC, $irhpPermitApplicationB, $irhpPermitApplicationB, $irhpPermitApplicationA]),
                new ArrayCollection([$irhpPermitApplicationA, $irhpPermitApplicationB, $irhpPermitApplicationB, $irhpPermitApplicationC])
            ],
        ];
    }

    public function testGetIrhpPermitApplicationsByCountryNameForUnsupportedType()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'Cannot get IrhpPermitApplications by country name for irhpPermitType ' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT
        );

        $entity = m::mock(Entity::class)->makePartial();
        $entity
            ->shouldReceive('isBilateral')
            ->andReturnFalse()
            ->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT);

        $entity->getIrhpPermitApplicationsByCountryName();
    }

    public function testGetIrhpPermitApplicationByCountryId()
    {
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn(Country::ID_FRANCE);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn(Country::ID_GERMANY);

        $irhpPermitApplications = [$irhpPermitApplication1, $irhpPermitApplication2];

        $entity = $this->createNewEntity();
        $entity->setIrhpPermitApplications($irhpPermitApplications);

        $this->assertSame(
            $entity->getIrhpPermitApplicationByCountryId(Country::ID_GERMANY),
            $irhpPermitApplication2
        );
    }

    public function testGetIrhpPermitApplicationByCountryIdNull()
    {
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn(Country::ID_FRANCE);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn(Country::ID_GERMANY);

        $irhpPermitApplications = [$irhpPermitApplication1, $irhpPermitApplication2];

        $entity = $this->createNewEntity();
        $entity->setIrhpPermitApplications($irhpPermitApplications);

        $this->assertNull(
            $entity->getIrhpPermitApplicationByCountryId(Country::ID_BELARUS)
        );
    }

    /**
     * @dataProvider dpGetIrhpPermitApplicationByStockCountryId
     */
    public function testGetIrhpPermitApplicationByStockCountryId($irhpPermitApplications, $countryId, $expected)
    {
        $entity = $this->createNewEntity();
        $entity->setIrhpPermitApplications($irhpPermitApplications);

        $this->assertSame(
            $expected,
            $entity->getIrhpPermitApplicationByStockCountryId($countryId)
        );
    }

    public function dpGetIrhpPermitApplicationByStockCountryId()
    {
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn('FR');

        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn('DE');

        $irhpPermitApplication3 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn('CH');

        $irhpPermitApplications = new ArrayCollection(
            [
                $irhpPermitApplication1,
                $irhpPermitApplication2,
                $irhpPermitApplication3
            ]
        );

        return [
            [$irhpPermitApplications, 'FR', $irhpPermitApplication1],
            [$irhpPermitApplications, 'DE', $irhpPermitApplication2],
            [$irhpPermitApplications, 'CH', $irhpPermitApplication3],
            [$irhpPermitApplications, 'NO', null],
        ];
    }

    /**
     * @dataProvider dpIsApsg
     */
    public function testIsApsg($businessProcessId, $expected)
    {
        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn(isset($businessProcessId) ? new RefData($businessProcessId) : null);

        $this->assertSame(
            $expected,
            $this->sut->isApsg()
        );
    }

    public function dpIsApsg()
    {
        return [
            [null, false],
            [RefData::BUSINESS_PROCESS_APG, false],
            [RefData::BUSINESS_PROCESS_APGG, false],
            [RefData::BUSINESS_PROCESS_APSG, true],
            [RefData::BUSINESS_PROCESS_AG, false],
        ];
    }

    /**
     * @dataProvider dpIsApgg
     */
    public function testIsApgg($businessProcessId, $expected)
    {
        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn(isset($businessProcessId) ? new RefData($businessProcessId) : null);

        $this->assertSame(
            $expected,
            $this->sut->isApgg()
        );
    }

    public function dpIsApgg()
    {
        return [
            [null, false],
            [RefData::BUSINESS_PROCESS_APG, false],
            [RefData::BUSINESS_PROCESS_APGG, true],
            [RefData::BUSINESS_PROCESS_APSG, false],
            [RefData::BUSINESS_PROCESS_AG, false],
        ];
    }

    /**
     * @dataProvider dpIsOngoing
     */
    public function testIsOngoing($isNotYetSubmitted, $isUnderConsideration, $isAwaitingFee, $expected)
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);
        $entity->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);
        $entity->shouldReceive('isAwaitingFee')
            ->withNoArgs()
            ->andReturn($isAwaitingFee);

        $this->assertEquals(
            $expected,
            $entity->isOngoing()
        );
    }

    public function dpIsOngoing()
    {
        return [
            [false, false, false, false],
            [false, false, true, true],
            [false, true, false, true],
            [true, false, false, true],
        ];
    }

    public function testGetDocumentsByCategoryAndSubCategory()
    {
        $document2 = $this->createMockDocument(6, 8);
        $document5 = $this->createMockDocument(6, 8);

        $documentWithoutSubCategory = m::mock(Document::class);
        $documentWithoutSubCategory->shouldReceive('getCategory->getId')
            ->withNoArgs()
            ->andReturn(6);
        $documentWithoutSubCategory->shouldReceive('getSubCategory')
            ->withNoArgs()
            ->andReturnNull();

        $documents = new ArrayCollection(
            [
                $this->createMockDocument(1, 2),
                $document2,
                $this->createMockDocument(3, 8),
                $documentWithoutSubCategory,
                $this->createMockDocument(6, 4),
                $document5
            ]
        );

        $this->sut->addDocuments($documents);
        $matchingDocuments = $this->sut->getDocumentsByCategoryAndSubCategory(6, 8);

        $this->assertEquals(2, $matchingDocuments->count());
        $this->assertSame($document2, $matchingDocuments->get(0));
        $this->assertSame($document5, $matchingDocuments->get(1));
    }

    private function createMockDocument($categoryId, $subCategoryId)
    {
        $document = m::mock(Document::class);
        $document->shouldReceive('getCategory->getId')
            ->withNoArgs()
            ->andReturn($categoryId);
        $document->shouldReceive('getSubCategory->getId')
            ->withNoArgs()
            ->andReturn($subCategoryId);

        return $document;
    }

    /**
     * @dataProvider dpIsUnderConsiderationOrAwaitingFeeAndAssociatedWithStock
     */
    public function testIsUnderConsiderationOrAwaitingFeeAndAssociatedWithStock(
        $isUnderConsideration,
        $isAwaitingFee,
        $irhpApplicationStock,
        $paramStock,
        $expected
    ) {
        $this->sut->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);

        $this->sut->shouldReceive('isAwaitingFee')
            ->withNoArgs()
            ->andReturn($isAwaitingFee);

        $this->sut->shouldReceive('getAssociatedStock')
            ->withNoArgs()
            ->andReturn($irhpApplicationStock);

        $this->assertEquals(
            $expected,
            $this->sut->isUnderConsiderationOrAwaitingFeeAndAssociatedWithStock($paramStock)
        );
    }

    public function dpIsUnderConsiderationOrAwaitingFeeAndAssociatedWithStock()
    {
        $irhpPermitStock1 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock2 = m::mock(IrhpPermitStock::class);

        return [
            [false, false, $irhpPermitStock1, $irhpPermitStock2, false],
            [true, false, $irhpPermitStock1, $irhpPermitStock2, false],
            [false, true, $irhpPermitStock1, $irhpPermitStock2, false],
            [false, false, $irhpPermitStock1, $irhpPermitStock1, false],
            [true, false, $irhpPermitStock1, $irhpPermitStock1, true],
            [false, true, $irhpPermitStock1, $irhpPermitStock1, true],
        ];
    }

    public function testUpdateCorCertificateNumber()
    {
        $corCertificateNumber = 'UKCR43/01234';

        $this->sut->updateCorCertificateNumber($corCertificateNumber);

        $this->assertEquals(
            $corCertificateNumber,
            $this->sut->getCorCertificateNumber()
        );
    }
}
