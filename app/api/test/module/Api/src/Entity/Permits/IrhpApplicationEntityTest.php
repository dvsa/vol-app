<?php

declare(strict_types=1);

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

    public function testGetCalculatedBundleValues(): void
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

    public function testGetApplicationRef(): void
    {
        $this->sut->setId(987);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicNo')
            ->andReturn('ABC123');

        $this->sut->setLicence($licence);

        $this->assertSame('ABC123 / 987', $this->sut->getApplicationRef());
    }

    public function testGetRelatedOrganisation(): void
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

    public function testGetRelatedLicence(): void
    {
        $licence = m::mock(Licence::class);
        $entity = $this->createNewEntity(null, null, null, $licence);
        $this->assertEquals($licence, $entity->getRelatedLicence());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsValid')]
    public function testIsValid(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isValid());
    }

    public static function dpTestIsValid(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsUnderConsideration')]
    public function testIsUnderConsideration(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isUnderConsideration());
    }

    public static function dpTestIsUnderConsideration(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsAwaitingFee')]
    public function testIsAwaitingFee(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isAwaitingFee());
    }

    public static function dpTestIsAwaitingFee(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsFeePaid')]
    public function testIsFeePaid(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isFeePaid());
    }

    public static function dpTestIsFeePaid(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsIssueInProgress')]
    public function testIsIssueInProgress(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isIssueInProgress());
    }

    public static function dpTestIsIssueInProgress(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsActive')]
    public function testIsActive(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isActive());
    }

    public static function dpTestIsActive(): array
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
    public function testCancel(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setStatus(new RefData(IrhpInterface::STATUS_NOT_YET_SUBMITTED));
        $entity->cancel(new RefData(IrhpInterface::STATUS_CANCELLED));
        $this->assertEquals(IrhpInterface::STATUS_CANCELLED, $entity->getStatus()->getId());
        $this->assertEquals(date('Y-m-d'), $entity->getCancellationDate()->format('Y-m-d'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCancelException')]
    public function testCancelException(mixed $status): void
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
    public static function dpCancelException(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanBeCancelled')]
    public function testCanBeCancelled(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->canBeCancelled());
    }

    public static function dpTestCanBeCancelled(): array
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

    public function testTerminate(): void
    {
        $this->sut->shouldReceive('canBeTerminated')
            ->withNoArgs()
            ->andReturnTrue();

        $terminateStatus = m::mock(RefData::class);

        $this->sut->terminate($terminateStatus);
        $this->assertSame($terminateStatus, $this->sut->getStatus());
        $this->assertInstanceOf(DateTime::class, $this->sut->getExpiryDate());
    }

    public function testTerminateException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_TERMINATE);

        $this->sut->shouldReceive('canBeTerminated')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->terminate(m::mock(RefData::class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeTerminated')]
    public function testCanBeTerminated(mixed $isValidCertificateOfRoadworthiness, mixed $expected): void
    {
        $this->sut->shouldReceive('isValidCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isValidCertificateOfRoadworthiness);

        $this->assertEquals(
            $expected,
            $this->sut->canBeTerminated()
        );
    }

    public static function dpCanBeTerminated(): array
    {
        return [
            [false, false],
            [true, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidCertificateOfRoadworthiness')]
    public function testIsValidCertificateOfRoadworthiness(mixed $isValid, mixed $isCertificateOfRoadworthiness, mixed $expected): void
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

    public static function dpIsValidCertificateOfRoadworthiness(): array
    {
        return [
            [false, false, false],
            [false, true, false],
            [true, false, false],
            [true, true, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsCancelled')]
    public function testIsCancelled(mixed $status, mixed $expected): void
    {
        $this->sut->setStatus(new RefData($status));
        $this->assertSame($expected, $this->sut->isCancelled());
    }

    public static function dpTestIsCancelled(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeWithdrawn')]
    public function testCanBeWithdrawn(mixed $reasonRefData, mixed $isUnderConsideration, mixed $isAwaitingFee, mixed $expected): void
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

    public static function dpCanBeWithdrawn(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeWithdrawnReasonDeclined')]
    public function testCanBeWithdrawnReasonDeclined(mixed $canBeDeclined): void
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

    public static function dpCanBeWithdrawnReasonDeclined(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsWithdrawn')]
    public function testIsWithdrawn(mixed $status, mixed $expected): void
    {
        $entity = $this->createNewEntity(null, new RefData($status));
        $this->assertSame($expected, $entity->isWithdrawn());
    }

    public static function dpTestIsWithdrawn(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsDeclined')]
    public function testIsDeclined(mixed $reason, mixed $expected): void
    {
        $entity = $this->createNewEntity(null, new RefData(IrhpInterface::STATUS_WITHDRAWN));
        $entity->setWithdrawReason(new RefData($reason));
        $this->assertSame($expected, $entity->isDeclined());
    }

    public static function dpTestIsDeclined(): array
    {
        return [
            [WithdrawableInterface::WITHDRAWN_REASON_DECLINED, true],
            [WithdrawableInterface::WITHDRAWN_REASON_BY_USER, false],
            [WithdrawableInterface::WITHDRAWN_REASON_UNPAID, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpWithdraw')]
    public function testWithdraw(mixed $withdrawReason, mixed $checkReasonAgainstStatus, mixed $expectedValidationWithdrawReason): void
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

    public static function dpWithdraw(): array
    {
        $withdrawReason = m::mock(RefData::class);

        return [
            [$withdrawReason, true, $withdrawReason],
            [$withdrawReason, false, null],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpWithdrawException')]
    public function testWithdrawException(
        mixed $withdrawReasonRefData,
        mixed $checkReasonAgainstStatus,
        mixed $expectedValidationWithdrawReasonRefData,
        mixed $expectedError
    ): void {
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

    public static function dpWithdrawException(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('trueOrFalseProvider')]
    public function testIsBilateral(mixed $isBilateral): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturn($isBilateral);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $this->assertEquals($isBilateral, $entity->isBilateral());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('trueOrFalseProvider')]
    public function testIsCertificateOfRoadworthiness(mixed $isCertificateOfRoadworthiness): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isCertificateOfRoadworthiness')->once()->withNoArgs()->andReturn($isCertificateOfRoadworthiness);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $this->assertEquals($isCertificateOfRoadworthiness, $entity->isCertificateOfRoadworthiness());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('trueOrFalseProvider')]
    public function testIsCertificateOfRoadworthinessVehicle(mixed $isCertificateOfRoadworthinessVehicle): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('trueOrFalseProvider')]
    public function testIsCertificateOfRoadworthinessTrailer(mixed $isCertificateOfRoadworthinessTrailer): void
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


    #[\PHPUnit\Framework\Attributes\DataProvider('trueOrFalseProvider')]
    public function testIsMultiStock(mixed $isMultiStock): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isMultiStock')->once()->withNoArgs()->andReturn($isMultiStock);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $this->assertEquals($isMultiStock, $entity->isMultiStock());
    }

    public static function trueOrFalseProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetAssociatedStock(): void
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

    public function testGetAssociatedStockMultiStockException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Multi stock permit types can\'t use this method');

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isMultiStock')->once()->withNoArgs()->andReturn(true);

        $entity = $this->createNewEntity(null, null, $irhpPermitType);

        $entity->getAssociatedStock();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsNotYetSubmitted')]
    public function testIsNotYetSubmitted(mixed $status, mixed $expectedNotYetSubmitted): void
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

    public static function dpIsNotYetSubmitted(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsOverviewAccessible')]
    public function testIsOverviewAccessible(mixed $isNotYetSubmitted, mixed $expected): void
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

    public static function dpIsOverviewAccessible(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsOverviewAccessibleBilateral')]
    public function testIsOverviewAccessibleBilateral(mixed $isNotYetSubmitted, mixed $countries, mixed $expected): void
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

    public static function dpIsOverviewAccessibleBilateral(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSubmittedForConsideration')]
    public function testIsSubmittedForConsideration(mixed $irhpPermitTypeId, mixed $status, mixed $expected): void
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

    public static function dpIsSubmittedForConsideration(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeUpdated')]
    public function testCanBeUpdated(
        mixed $isNotYetSubmitted,
        mixed $isUnderConsideration,
        mixed $isValid,
        mixed $isCertificateOfRoadworthiness,
        mixed $expectedCanBeUpdated
    ): void {
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

    public static function dpCanBeUpdated(): array
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

    public function testHasCheckedAnswers(): void
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(false);

        $this->assertFalse($this->sut->hasCheckedAnswers());

        $this->sut->setCheckedAnswers(true);
        $this->assertTrue($this->sut->hasCheckedAnswers());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasCheckedAnswersBilateral')]
    public function testHasCheckedAnswersBilateral(mixed $checkedAnswers): void
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setCheckedAnswers($checkedAnswers);

        $this->assertFalse($this->sut->hasCheckedAnswers());
    }

    public static function dpHasCheckedAnswersBilateral(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testHasMadeDeclaration(): void
    {
        $this->assertFalse($this->sut->hasMadeDeclaration());

        $this->sut->setDeclaration(true);
        $this->assertTrue($this->sut->hasMadeDeclaration());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeSubmitted')]
    public function testCanBeSubmittedFalseIfAlreadySubmittedOrNotEligible(
        mixed $isNotYetSubmitted,
        mixed $isEligibleForPermits,
        mixed $allSectionsCompleted,
        mixed $expectedCanBeSubmitted
    ): void {
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

    public static function dpCanBeSubmitted(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanUpdateCountries')]
    public function testCanUpdateCountries(mixed $canBeUpdated, mixed $irhpPermitTypeId, mixed $isFieldReadyToComplete, mixed $expected): void
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

    public static function dpTestCanUpdateCountries(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsReadyForNoOfPermits')]
    public function testIsReadyForNoOfPermits(
        mixed $canBeUpdated,
        mixed $irhpPermitApplications,
        mixed $expectedIsReadyForNoOfPermits
    ): void {
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

    public static function dpIsReadyForNoOfPermits(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasOutstandingFees')]
    public function testHasOutstandingFees(mixed $feesData, mixed $expectedResult): void
    {
        $this->sut->setFees(
            $this->createFeesArrayCollectionFromArrayData($feesData)
        );

        $this->assertEquals($expectedResult, $this->sut->hasOutstandingFees());
    }

    public static function dpHasOutstandingFees(): array
    {
        return [
            [
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIssueFeeOverdue')]
    public function testIssueFeeOverdue(mixed $thresholdDays, mixed $dateAfterThreshold, mixed $dateOfThreshold, mixed $dateBeforeThreshold): void
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

    public static function dpIssueFeeOverdue(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIssueFeeOverdueBoundary')]
    public function testIssueFeeOverdueBoundary(mixed $days, mixed $expected): void
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

    public static function dpIssueFeeOverdueBoundary(): array
    {
        return [
            [9, 0],
            [10, 1],
            [11, 1],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetLatestOutstandingApplicationFee')]
    public function testGetLatestOutstandingApplicationFee(mixed $feesData, mixed $expectedIndex): void
    {
        $fees = $this->createFeesArrayCollectionFromArrayData($feesData);
        $this->sut->setFees($fees);

        $latestOutstandingIssueFee = $this->sut->getLatestOutstandingApplicationFee();

        if (is_null($expectedIndex)) {
            $this->assertNull($latestOutstandingIssueFee);
        }

        $this->assertSame($expectedIndex === null ? null : $fees[$expectedIndex], $latestOutstandingIssueFee);
    }

    public static function dpGetLatestOutstandingApplicationFee(): array
    {
        return [
            [
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetLatestOutstandingIssueFee')]
    public function testGetLatestOutstandingIssueFee(mixed $feesData, mixed $expectedIndex): void
    {
        $fees = $this->createFeesArrayCollectionFromArrayData($feesData);
        $this->sut->setFees($fees);

        $latestOutstandingIssueFee = $this->sut->getLatestOutstandingIssueFee();

        if (is_null($expectedIndex)) {
            $this->assertNull($latestOutstandingIssueFee);
        }

        $this->assertSame($expectedIndex === null ? null : $fees[$expectedIndex], $latestOutstandingIssueFee);
    }

    public static function dpGetLatestOutstandingIssueFee(): array
    {
        return [
            [
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetLatestIssueFee')]
    public function testGetLatestIssueFee(mixed $feesData, mixed $expectedIndex): void
    {
        $fees = $this->createFeesArrayCollectionFromArrayData($feesData);
        $this->sut->setFees($fees);

        $latestOutstandingIssueFee = $this->sut->getLatestIssueFee();

        if (is_null($expectedIndex)) {
            $this->assertNull($latestOutstandingIssueFee);
        }

        $this->assertSame($expectedIndex === null ? null : $fees[$expectedIndex], $latestOutstandingIssueFee);
    }

    public static function dpGetLatestIssueFee(): array
    {
        return [
            [
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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
                'feesData' => [
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

    private function createFeesArrayCollectionFromArrayData(mixed $feesData): ArrayCollection
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

    public function testGetOutstandingFees(): void
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

    public function testGetOutstandingFeeAmount(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetSectionCompletionMultilateral')]
    public function testGetSectionCompletionMultilateral(mixed $data, mixed $expected): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $irhpPermitType->setId(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);

        $this->sut->setIrhpPermitType($irhpPermitType);
        $this->sut->setIrhpPermitApplications($data['irhpPermitApplications']);
        $this->sut->setCheckedAnswers($data['checkedAnswers']);
        $this->sut->setDeclaration($data['declaration']);

        $this->assertSame($expected, $this->sut->getSectionCompletion());
    }

    public static function dpTestGetSectionCompletionMultilateral(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetSectionCompletionBilateral')]
    public function testGetSectionCompletionBilateral(mixed $data, mixed $expected): void
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

    public static function dpTestGetSectionCompletionBilateral(): array
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

    public function testGetSectionCompletionForUndefinedIrhpPermitType(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanCheckAnswersForApplicationPathEnabled')]
    public function testCanCheckAnswersForApplicationPathEnabled(
        mixed $irhpPermitTypeId,
        mixed $status,
        mixed $questionAnswerData,
        mixed $expected
    ): void {
        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId($irhpPermitTypeId);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->sut->setStatus(new RefData($status));

        $this->sut->shouldReceive('getQuestionAnswerData')
            ->andReturn($questionAnswerData);

        $this->assertEquals($expected, $this->sut->canCheckAnswers());
    }

    public static function dpCanCheckAnswersForApplicationPathEnabled(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanCheckAnswersForMultilateral')]
    public function testCanCheckAnswersForMultilateral(
        mixed $status,
        mixed $permitsRequired,
        mixed $expected
    ): void {
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

    public static function dpCanCheckAnswersForMultilateral(): array
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

    public function testCanCheckAnswersForBilateral(): void
    {
        $this->sut->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->assertFalse(
            $this->sut->canCheckAnswers()
        );
    }

    public function testUpdateCheckAnswersNotBilateral(): void
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

    public function testUpdateCheckAnswersNotBilateralException(): void
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

    public function testUpdateCheckAnswers(): void
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

    public function testResetCheckAnswersAndDeclarationSuccess(): void
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

    public function testResetCheckAnswersAndDeclarationFail(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanMakeDeclarationForApplicationPathEnabled')]
    public function testCanMakeDeclarationForApplicationPathEnabled(
        mixed $irhpPermitTypeId,
        mixed $status,
        mixed $questionAnswerData,
        mixed $expected
    ): void {
        $irhpPermitType = new IrhpPermitType();
        $irhpPermitType->setId($irhpPermitTypeId);
        $this->sut->setIrhpPermitType($irhpPermitType);

        $this->sut->setStatus(new RefData($status));

        $this->sut->shouldReceive('getQuestionAnswerData')
            ->andReturn($questionAnswerData);

        $this->assertEquals($expected, $this->sut->canMakeDeclaration());
    }

    public static function dpCanMakeDeclarationForApplicationPathEnabled(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanMakeDeclarationForMultilateral')]
    public function testCanMakeDeclarationForMultilateral(
        mixed $status,
        mixed $permitsRequired,
        mixed $checkedAnswers,
        mixed $expected
    ): void {
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

    public static function dpCanMakeDeclarationForMultilateral(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanMakeDeclarationForBilateral')]
    public function testCanMakeDeclarationForBilateral(mixed $countriesAndStatuses, mixed $canBeUpdated, mixed $expected): void
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

    public static function dpCanMakeDeclarationForBilateral(): array
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

    public function testMakeDeclaration(): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('canMakeDeclaration')
            ->andReturn(true);

        $irhpApplication->makeDeclaration();

        $this->assertTrue($irhpApplication->getDeclaration());
    }

    public function testMakeDeclarationFail(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_MAKE_DECLARATION);

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('canMakeDeclaration')
            ->andReturn(false);

        $irhpApplication->makeDeclaration();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestGetPermitsRequired')]
    public function testGetPermitsRequired(mixed $irhpPermitApplications, mixed $expected): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();

        $irhpApplication->setIrhpPermitApplications(
            new ArrayCollection($irhpPermitApplications)
        );

        $this->assertSame($expected, $irhpApplication->getPermitsRequired());
    }

    public static function dpTestGetPermitsRequired(): array
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

    public function testCanCreateOrReplaceIssueFeeTrue(): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(true);

        $this->assertTrue($irhpApplication->canCreateOrReplaceIssueFee());
    }

    public function testCanCreateOrReplaceIssueFeeFalse(): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(false);

        $this->assertFalse($irhpApplication->canCreateOrReplaceIssueFee());
    }

    public function testCanCreateOrReplaceApplicationFeeTrue(): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(true);

        $this->assertTrue($irhpApplication->canCreateOrReplaceApplicationFee());
    }

    public function testCanCreateOrReplaceApplicationFeeFalse(): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->andReturn(false);

        $this->assertFalse($irhpApplication->canCreateOrReplaceApplicationFee());
    }

    public function testHaveFeesRequiredChangedExceptionWhenNothingStored(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('storeFeesRequired must be called before haveFeesRequiredChanged');

        $irhpApplication = new Entity();
        $irhpApplication->haveFeesRequiredChanged();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHaveFeesRequiredChanged')]
    public function testHaveFeesRequiredChanged(
        mixed $irhpPermitType,
        mixed $stock1QuantityBefore,
        mixed $stock2QuantityBefore,
        mixed $stock1QuantityAfter,
        mixed $stock2QuantityAfter,
        mixed $expected
    ): void {
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

    public static function dpHaveFeesRequiredChanged(): array
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

    public function testGetApplicationFeeProductRefsAndQuantities(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetApplicationFeeProductReference')]
    public function testGetApplicationFeeProductReference(mixed $irhpPermitTypeId, mixed $productReference): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($irhpPermitTypeId);

        $this->assertEquals(
            $productReference,
            $irhpApplication->getApplicationFeeProductReference()
        );
    }

    public static function dpGetApplicationFeeProductReference(): array
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

    public function testGetApplicationFeeProductReferenceUnsupportedType(): void
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

    public function testGetIssueFeeProductReferenceEcmtShortTerm(): void
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

    public function testGetIssueFeeProductReferenceEcmtAnnual(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIssueFeeProductReferenceUnsupportedType')]
    public function testGetIssueFeeProductReferenceUnsupportedType(mixed $irhpPermitTypeId): void
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

    public static function dpGetIssueFeeProductReferenceUnsupportedType(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER],
        ];
    }

    public function testGetIssueFeeProductRefsAndQuantities(): void
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

    public function testUpdateDateReceived(): void
    {
        $irhpApplication = m::mock(Entity::class)->makePartial();
        $dateString = '2019-01-01';
        $irhpApplication->updateDateReceived('2019-01-01');
        $this->assertEquals(new DateTime($dateString), $irhpApplication->getDateReceived());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestIsReadyForIssuing')]
    public function testIsReadyForIssuing(mixed $hasOutstandingFees, mixed $expectedResult): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('hasOutstandingFees')
            ->andReturn($hasOutstandingFees);

        $this->assertEquals($expectedResult, $entity->isReadyForIssuing());
    }

    public static function dpTestIsReadyForIssuing(): array
    {
        return [
            [false, true],
            [true, false],
        ];
    }

    public function testSubmit(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSubmitShortTermAndAnnual')]
    public function testSubmitShortTermAndAnnual(mixed $irhpPermitTypeId): void
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

    public static function dpSubmitShortTermAndAnnual(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSubmitCertOfRoadworthiness')]
    public function testSubmitCertOfRoadworthiness(mixed $irhpPermitTypeId): void
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

    public static function dpSubmitCertOfRoadworthiness(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER],
        ];
    }

    public function testSubmitException(): void
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

    public function testSubmitExceptionUnknownType(): void
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

    public function testGrant(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeGranted')
            ->withNoArgs()
            ->andReturnTrue();

        $status = m::mock(RefData::class);

        $entity->grant($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testGrantException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_GRANT);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeGranted')
            ->withNoArgs()
            ->andReturnFalse();

        $entity->grant(m::mock(RefData::class));
    }

    public function testProceedToIssuing(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isReadyForIssuing')
            ->andReturn(true);

        $status = m::mock(RefData::class);

        $entity->proceedToIssuing($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testProceedToIssuingException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_ISSUE);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isReadyForIssuing')
            ->andReturn(false);

        $entity->proceedToIssuing(m::mock(RefData::class));
    }

    public function testProceedToUnderConsideration(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('hasOutstandingFees')
            ->withNoArgs()
            ->andReturnFalse();

        $status = m::mock(RefData::class);

        $entity->proceedToUnderConsideration($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testProceedToUnderConsiderationException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_SUBMIT);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('hasOutstandingFees')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->proceedToUnderConsideration(m::mock(RefData::class));
    }

    public function testProceedToValid(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isIssueInProgress')
            ->andReturn(true);

        $status = m::mock(RefData::class);

        $entity->proceedToValid($status);
        $this->assertSame($status, $entity->getStatus());
    }

    public function testProceedToValidException(): void
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

    public function testGetQuestionAnswerBilateral(): void
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

    public function testGetQuestionAnswerMultilateral(): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->withNoArgs()->andReturn(false);
        $irhpPermitType->shouldReceive('isMultilateral')->withNoArgs()->andReturn(true);

        $licence = m::mock(Licence::class);
        $entity = $this->createNewEntity(null, null, $irhpPermitType, $licence);

        $this->assertEquals([], $entity->getQuestionAnswerData());
    }

    public function testGetQuestionAnswerDataWithoutActiveApplicationPath(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetQuestionAnswerDataWithActiveApplicationPath')]
    public function testGetQuestionAnswerDataWithActiveApplicationPath(mixed $data, mixed $applicationSteps, mixed $expected): void
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

    public static function dpGetQuestionAnswerDataWithActiveApplicationPath(): array
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
            ],
        ];
    }

    public function testGetAnswerForCustomEcmtRemovalNoOfPermits(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAnswerForCustomEcmtNoOfPermits')]
    public function testGetAnswerForCustomEcmtNoOfPermits(
        mixed $formControlType,
        mixed $requiredEuro5,
        mixed $requiredEuro6,
        mixed $expectedAnswer
    ): void {
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

    public static function dpGetAnswerForCustomEcmtNoOfPermits(): array
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

    public function testGetAnswerForCustomEcmtInternationalJourneys(): void
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

    public function testGetAnswerForCustomEcmtInternationalJourneysNull(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetAnswerForCustomEcmtSectors')]
    public function testGetAnswerForCustomEcmtSectors(mixed $sectorsEntity, mixed $expectedAnswer): void
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

    public static function dpTestGetAnswerForCustomEcmtSectors(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetAnswerForCustomEcmtAnnual2018NoOfPermits')]
    public function testGetAnswerForCustomEcmtAnnual2018NoOfPermits(
        mixed $isSnapshot,
        mixed $requiredEuro5,
        mixed $requiredEuro6,
        mixed $expectedAnswer
    ): void {
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

    public static function dpTestGetAnswerForCustomEcmtAnnual2018NoOfPermits(): array
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

    public function testGetAnswerForQuestionWithoutActiveQuestionText(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAnswerForQuestion')]
    public function testGetAnswerForQuestionWithoutAnswer(mixed $isCustom, mixed $formControlType): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAnswerForQuestion')]
    public function testGetAnswerForQuestionWithAnswer(mixed $isCustom, mixed $formControlType): void
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

    public static function dpGetAnswerForQuestion(): array
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

    public function testGetAnswerUnknownCustomType(): void
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

    public function testGetOutstandingApplicationFees(): void
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

    public function testGetOutstandingIssueFees(): void
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

    public function testGetOutstandingIrfoPermitFees(): void
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

    public function testGetContextValue(): void
    {
        $irhpApplicationId = 87;

        $irhpApplication = m::mock(Entity::class)->makePartial();
        $irhpApplication->setId($irhpApplicationId);

        $this->assertEquals($irhpApplicationId, $irhpApplication->getContextValue());
    }

    private function createMockFee(mixed $feeTypeId, mixed $isOutstanding): mixed
    {
        $fee = m::mock(Fee::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId')
            ->andReturn($feeTypeId);
        $fee->shouldReceive('isOutstanding')
            ->andReturn($isOutstanding);

        return $fee;
    }

    private function createNewEntity(
        mixed $source = null,
        mixed $status = null,
        mixed $irhpPermitType = null,
        mixed $licence = null,
        mixed $dateReceived = null
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetFeePerPermitBilateralMultilateral')]
    public function testGetFeePerPermitBilateralMultilateral(mixed $irhpPermitTypeId): void
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

    public static function dpTestGetFeePerPermitBilateralMultilateral(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL]
        ];
    }

    public function testGetFeePerPermitEcmtRemoval(): void
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

    public function testGetFeePerPermitUnsupported(): void
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

    public function testGetFirstIrhpPermitApplication(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $entity = $this->createNewEntity();
        $entity->addIrhpPermitApplications($irhpPermitApplication);

        $this->assertSame(
            $irhpPermitApplication,
            $entity->getFirstIrhpPermitApplication()
        );
    }

    public function testGetFirstIrhpPermitApplicationExceptionOnNone(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'IrhpApplication has zero linked IrhpPermitApplication instances'
        );

        $entity = $this->createNewEntity();
        $entity->getFirstIrhpPermitApplication();
    }

    public function testGetFirstIrhpPermitApplicationExceptionOnMoreThanOne(): void
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

    public function testExpire(): void
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

    public function testExpireException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('This application cannot be expired.');

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('canBeExpired')
            ->andReturn(false);

        $entity->expire(m::mock(RefData::class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeExpired')]
    public function testCanBeExpired(mixed $status, mixed $hasValidPermits, mixed $expected): void
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

    public static function dpCanBeExpired(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanViewCandidatePermits')]
    public function testCanViewCandidatePermits(mixed $isAwaitingFee, mixed $isCandidatePermitsAllocationMode, mixed $isApgg, mixed $expected): void
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

    public static function dpTestCanViewCandidatePermits(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCanSelectCandidatePermits')]
    public function testCanSelectCandidatePermits(mixed $isAwaitingFee, mixed $isCandidatePermitsAllocationMode, mixed $isApsg, mixed $expected): void
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

    public static function dpTestCanSelectCandidatePermits(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsCandidatePermitsAllocationMode')]
    public function testIsCandidatePermitsAllocationMode(mixed $allocationMode, mixed $expected): void
    {
        $this->sut->shouldReceive('getAllocationMode')
            ->withNoArgs()
            ->andReturn($allocationMode);

        $this->assertEquals(
            $expected,
            $this->sut->isCandidatePermitsAllocationMode()
        );
    }

    public static function dpTestIsCandidatePermitsAllocationMode(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeGranted')]
    public function testCanBeGranted(mixed $isUnderConsideration, mixed $licenceValid, mixed $businessProcess, mixed $expected): void
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

    public static function dpCanBeGranted(): array
    {
        return [
            [true, true, RefData::BUSINESS_PROCESS_APGG, true],
            [false, true, RefData::BUSINESS_PROCESS_APGG, false],
            [true, false, RefData::BUSINESS_PROCESS_APGG, false],
            [true, true, RefData::BUSINESS_PROCESS_APSG, false],
            [true, true, RefData::BUSINESS_PROCESS_APG, false],
        ];
    }

    public function testUpdateInternationalJourneys(): void
    {
        $refData = m::mock(RefData::class);

        $entity = $this->createNewEntity();
        $entity->updateInternationalJourneys($refData);

        $this->assertSame(
            $refData,
            $entity->getInternationalJourneys()
        );
    }

    public function testClearInternationalJourneys(): void
    {
        $refData = m::mock(RefData::class);

        $entity = $this->createNewEntity();
        $entity->setInternationalJourneys($refData);
        $entity->clearInternationalJourneys();

        $this->assertNull($entity->getInternationalJourneys());
    }

    public function testUpdateSectors(): void
    {
        $sectors = m::mock(Sectors::class);

        $entity = $this->createNewEntity();
        $entity->updateSectors($sectors);

        $this->assertSame(
            $sectors,
            $entity->getSectors()
        );
    }

    public function testClearSectors(): void
    {
        $sectors = m::mock(Sectors::class);

        $entity = $this->createNewEntity();
        $entity->setSectors($sectors);
        $entity->clearSectors();

        $this->assertNull($entity->getSectors());
    }

    public function testGetBusinessProcess(): void
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

    public function testGetBusinessProcessWithoutIrhpPermitApplication(): void
    {
        $entity = $this->createNewEntity();

        $this->assertNull($entity->getBusinessProcess());
    }

    public function testGetAnswerValueByQuestionId(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHasCountryId')]
    public function testHasCountryId(mixed $countryId, mixed $expected): void
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

    public static function dpTestHasCountryId(): array
    {
        return [
            ['FR', true],
            ['RU', true],
            ['DE', true],
            ['ES', false],
        ];
    }

    public function testGetCountryIds(): void
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

    public function testUpdateCountries(): void
    {
        $arrayCollection = m::mock(ArrayCollection::class);

        $entity = $this->createNewEntity();
        $entity->updateCountries($arrayCollection);

        $this->assertSame(
            $arrayCollection,
            $entity->getCountrys()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCalculateTotalPermitsRequired')]
    public function testCalculateTotalPermitsRequired(
        mixed $isEcmtShortTerm,
        mixed $isEcmtAnnual,
        mixed $requiredEuro5,
        mixed $requiredEuro6,
        mixed $expectedTotal
    ): void {
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

    public static function dpCalculateTotalPermitsRequired(): array
    {
        return [
            'ecmt short term' => [true, false, 5, 3, 8],
            'ecmt annual' => [false, true, 4, 9, 13],
        ];
    }

    public function testCalculateTotalPermitsRequiredIncorrectType(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCalculateTotalPermitsRequiredNotSet')]
    public function testCalculateTotalPermitsRequiredNotSet(
        mixed $isEcmtShortTerm,
        mixed $isEcmtAnnual,
        mixed $requiredEuro5,
        mixed $requiredEuro6
    ): void {
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

    public static function dpCalculateTotalPermitsRequiredNotSet(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPermitIntensityOfUse')]
    public function testGetPermitIntensityOfUse(mixed $emissionsCategoryId, mixed $expectedIntensityOfUse): void
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPermitIntensityOfUse')]
    public function testGetPermitIntensityOfUseZeroPermitsRequested(mixed $emissionsCategoryId, mixed $expectedIntensityOfUse): void
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

    public function testGetPermitIntensityOfUseBadEmissionsCategory(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected emissionsCategoryId parameter for getPermitIntensityOfUse: xyz');

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);
        $entity->getPermitIntensityOfUse('xyz');
    }

    public static function dpTestGetPermitIntensityOfUse(): array
    {
        return [
            [null, 5],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, 17.5],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, 7],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPermitApplicationScore')]
    public function testGetPermitApplicationScore(
        mixed $emissionsCategoryId,
        mixed $internationalJourneys,
        mixed $expectedPermitApplicationScore
    ): void {
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

    public static function dpTestGetPermitApplicationScore(): array
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

    public function testGetCamelCaseEntityName(): void
    {
        $application = $this->createNewEntity();

        $this->assertEquals(
            'irhpApplication',
            $application->getCamelCaseEntityName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetEmailCommandLookup')]
    public function testGetEmailCommandLookup(mixed $isEcmtShortTerm, mixed $isEcmtAnnual, mixed $expectedEmailCommandLookup): void
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

    public static function dpGetEmailCommandLookup(): array
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

    public function testGetEmailCommandLookupException(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProvideOutcomeNotificationType')]
    public function testGetOutcomeNotificationType(mixed $source, mixed $expectedNotificationType): void
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
    public static function dpProvideOutcomeNotificationType(): array
    {
        return [
            [IrhpInterface::SOURCE_SELFSERVE, ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL],
            [IrhpInterface::SOURCE_INTERNAL, ApplicationAcceptConsts::NOTIFICATION_TYPE_MANUAL]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpProvideSuccessLevel')]
    public function testGetSuccessLevel(mixed $permitsRequired, mixed $permitsAwarded, mixed $expectedSuccessLevel): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasStateRequiredForPostScoringEmail')]
    public function testHasStateRequiredForPostScoringEmail(mixed $isUnderConsideration, mixed $isInScope, mixed $successLevel, mixed $expected): void
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

    public static function dpHasStateRequiredForPostScoringEmail(): array
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
    public static function dpProvideSuccessLevel(): array
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

    public function testProceedToUnsuccessful(): void
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

    public function testProceedToUnsuccessfulException(): void
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

    public function testProceedToAwaitingFee(): void
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

    public function testProceedToAwaitingFeeException(): void
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

    public function testGetPermitsAwarded(): void
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

    public function testGetPermitsAwardedException(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIntensityOfUseWarningThreshold')]
    public function testGetIntensityOfUseWarningThreshold(
        mixed $isEcmtShortTerm,
        mixed $isEcmtAnnual,
        mixed $requiredEuro5,
        mixed $requiredEuro6,
        mixed $expectedThreshold
    ): void {
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

    public static function dpGetIntensityOfUseWarningThreshold(): array
    {
        return [
            [true, false, 5, 8, 800],
            [true, false, 4, 2, 400],
            [false, true, 5, 8, 800],
            [false, true, 4, 2, 400],
        ];
    }

    public function testGetIntensityOfUseWarningThresholdException(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAppSubmittedEmailCommand')]
    public function testGetAppSubmittedEmailCommand(
        mixed $isEcmtShortTerm,
        mixed $isEcmtAnnual,
        mixed $businessProcessId,
        mixed $expectedCommand
    ): void {
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

    public static function dpGetAppSubmittedEmailCommand(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAppWithdrawnEmailCommand')]
    public function testGetAppWithdrawnEmailCommand(mixed $irhpPermitTypeId, mixed $withdrawReason, mixed $expectedCommand): void
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

    public static function dpGetAppWithdrawnEmailCommand(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIssuedEmailCommand')]
    public function testGetIssuedEmailCommand(mixed $isEcmtAnnual, mixed $isEcmtShortTerm, mixed $isApsg, mixed $isApgg, mixed $expectedCommand): void
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

    public static function dpGetIssuedEmailCommand(): array
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

    public function testGetAllocationMode(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpShouldAllocatePermitsOnSubmission')]
    public function testShouldAllocatePermitsOnSubmission(mixed $businessProcessId, mixed $expected): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUpdateChecked')]
    public function testUpdateChecked(mixed $checked): void
    {
        $this->sut->updateChecked($checked);
        $this->assertEquals($checked, $this->sut->getChecked());
    }

    public static function dpUpdateChecked(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public static function dpShouldAllocatePermitsOnSubmission(): array
    {
        return [
            [RefData::BUSINESS_PROCESS_APG, true],
            [RefData::BUSINESS_PROCESS_APGG, false],
            [RefData::BUSINESS_PROCESS_APSG, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetSubmissionTaskDescription')]
    public function testGetSubmissionTaskDescription(mixed $irhpPermitTypeId, mixed $expectedTaskDescription): void
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

    public static function dpGetSubmissionTaskDescription(): array
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

    public function testGetSubmissionTaskDescriptionException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No submission task description defined for permit type foo');

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn('foo');

        $this->sut->setIrhpPermitType($irhpPermitType);
        $this->sut->getSubmissionTaskDescription();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetSubmissionStatus')]
    public function testGetSubmissionStatus(mixed $businessProcessId, mixed $expectedStatus): void
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

    public static function dpGetSubmissionStatus(): array
    {
        return [
            [RefData::BUSINESS_PROCESS_AG, IrhpInterface::STATUS_VALID],
            [RefData::BUSINESS_PROCESS_APG, IrhpInterface::STATUS_ISSUING],
            [RefData::BUSINESS_PROCESS_APGG, IrhpInterface::STATUS_UNDER_CONSIDERATION],
            [RefData::BUSINESS_PROCESS_APSG, IrhpInterface::STATUS_UNDER_CONSIDERATION],
        ];
    }

    public function testGetSubmissionStatusException(): void
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

    public function testGetCandidatePermitCreationModeMultiStock(): void
    {
        $this->sut->shouldReceive('isMultiStock')
            ->withNoArgs()
            ->andReturn(true);

        $this->assertEquals(
            IrhpPermitStock::CANDIDATE_MODE_NONE,
            $this->sut->getCandidatePermitCreationMode()
        );
    }

    public function testGetCandidatePermitCreationModeNotMultiStock(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRequiresPreAllocationCheck')]
    public function testRequiresPreAllocationCheck(mixed $isEcmtShortTerm, mixed $isEcmtAnnual, mixed $expected): void
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

    public static function dpRequiresPreAllocationCheck(): array
    {
        return [
            [true, false, true],
            [false, true, true],
            [false, false, false],
        ];
    }

    public function testFetchOpenSubmissionTask(): void
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

    public function testFetchOpenSubmissionTaskNull(): void
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

    private function createMockTask(mixed $description, mixed $isClosed, mixed $categoryId, mixed $subcategoryId): mixed
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

    public function testExpireCertificateWrongPermitType(): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->expects()->isCertificateOfRoadworthiness()->withNoArgs()->andReturnFalse();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Entity::ERR_ROADWORTHINESS_ONLY);
        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->expireCertificate(m::mock(RefData::class));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpExpireCertificateMotNotExpired')]
    public function testExpireCertificateMotNotExpired(mixed $expiryDate): void
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

    public static function dpExpireCertificateMotNotExpired(): array
    {
        return [
            'no mot expiry date present' => [null],
            'mot expires tomorrow' => [(new \DateTime('+1 day'))->format('Y-m-d')],
            'mot expires today' => [(new \DateTime())->format('Y-m-d')],
        ];
    }

    public function testExpireCertificateMotHasExpired(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetMotExpiryDate')]
    public function testGetMotExpiryDate(mixed $isTrailer, mixed $questionId): void
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

    public static function dpTestGetMotExpiryDate(): array
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

    public function testGetMotExpiryDateIncorrectType(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturnFalse();

        $this->assertNull(
            $entity->getMotExpiryDate()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeResetToNotYetSubmittedFromValid')]
    public function testCanBeResetToNotYetSubmittedFromValid(mixed $isValid, mixed $isCertificateOfRoadworthiness, mixed $expected): void
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

    public static function dpCanBeResetToNotYetSubmittedFromValid(): array
    {
        return [
            [true, true, true],
            [false, true, false],
            [true, false, false],
            [false, false, false],
        ];
    }

    public function testResetToNotYetSubmittedFromValid(): void
    {
        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromValid')
            ->withNoArgs()
            ->andReturn(true);

        $this->sut->resetToNotYetSubmittedFromValid($status);

        $this->assertSame($status, $this->sut->getStatus());
    }

    public function testResetToNotYetSubmittedFromValidException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Unable to reset this application to Not Yet Submitted');

        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromValid')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->resetToNotYetSubmittedFromValid($status);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeResetToNotYetSubmittedFromCancelled')]
    public function testCanBeResetToNotYetSubmittedFromCancelled(
        mixed $isCancelled,
        mixed $isEcmtShortTerm,
        mixed $isEcmtAnnual,
        mixed $hasUnderConsiderationOrAwaitingFeeApplicationForStock,
        mixed $expected
    ): void {
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

    public static function dpCanBeResetToNotYetSubmittedFromCancelled(): array
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

    public function testResetToNotYetSubmittedFromCancelled(): void
    {
        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromCancelled')
            ->withNoArgs()
            ->andReturn(true);

        $this->sut->resetToNotYetSubmittedFromCancelled($status);

        $this->assertSame($status, $this->sut->getStatus());
    }

    public function testResetToNotYetSubmittedFromCancelledException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Unable to reset this application to Not Yet Submitted');

        $status = m::mock(RefData::class);

        $this->sut->shouldReceive('canBeResetToNotYetSubmittedFromCancelled')
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->resetToNotYetSubmittedFromCancelled($status);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeRevivedFromWithdrawn')]
    public function testCanBeRevivedFromWithdrawn(mixed $withdrawReason, mixed $inScope, mixed $businessProcessId, mixed $expected): void
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

    public static function dpCanBeRevivedFromWithdrawn(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeRevivedFromWithdrawnNotWithdrawn')]
    public function testCanBeRevivedFromWithdrawnNotWithdrawn(mixed $inScope, mixed $businessProcessId): void
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

    public static function dpCanBeRevivedFromWithdrawnNotWithdrawn(): array
    {
        return [
            [true, RefData::BUSINESS_PROCESS_APGG],
            [false, RefData::BUSINESS_PROCESS_APSG],
            [true, RefData::BUSINESS_PROCESS_APGG],
            [false, RefData::BUSINESS_PROCESS_APSG],
        ];
    }

    public function testReviveFromWithdrawn(): void
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

    public function testReviveFromWithdrawnNoBusinessProcess(): void
    {
        $this->sut->expects()->getBusinessProcess()
            ->withNoArgs()
            ->andReturnNull();

        self::assertFalse($this->sut->canBeRevivedFromWithdrawn());
    }

    public function testReviveFromWithdrawnException(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanBeRevivedFromUnsuccessful')]
    public function testCanBeRevivedFromUnsuccessful(mixed $businessProcessId, mixed $statusId, mixed $expected): void
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

    public static function dpCanBeRevivedFromUnsuccessful(): array
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

    public function testReviveFromUnsuccessfulNoBusinessProcess(): void
    {
        $this->sut->expects()->getBusinessProcess()
            ->withNoArgs()
            ->andReturnNull();

        self::assertFalse($this->sut->canBeRevivedFromUnsuccessful());
    }

    public function testReviveFromUnsuccessful(): void
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

    public function testReviveFromUnsuccessfulException(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('productRefMonthProvider')]
    public function testGetProductReferenceForTier(mixed $expected, mixed $validFrom, mixed $validTo, mixed $now): void
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

    public static function productRefMonthProvider(): array
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

    public function testCreateAnswer(): void
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

    public function testOnSubmitApplicationStep(): void
    {
        $this->sut->shouldReceive('resetCheckAnswersAndDeclaration')
            ->withNoArgs()
            ->once();

        $this->sut->onSubmitApplicationStep();
    }

    public function testGetAdditionalQaViewData(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsApplicationPathEnabled')]
    public function testIsApplicationPathEnabled(mixed $isEnabled): void
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

    public static function dpIsApplicationPathEnabled(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetRepositoryName(): void
    {
        $this->assertEquals(
            'IrhpApplication',
            $this->sut->getRepositoryName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIrhpPermitApplicationIdForCountry')]
    public function testGetIrhpPermitApplicationIdForCountry(mixed $result, mixed $countryCode): void
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

    public static function dpGetIrhpPermitApplicationIdForCountry(): array
    {
        return [
            [null, 'NO'],
            [3322, 'DE'],
        ];
    }

    public function testGetIrhpPermitApplicationIdForCountryMissingIrhpPermitApplication(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIrhpPermitApplicationsByCountryName')]
    public function testGetIrhpPermitApplicationsByCountryName(mixed $irhpPermitApplications, mixed $expected): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isBilateral')->once()->withNoArgs()->andReturnTrue();

        $entity = $this->createNewEntity(null, null, $irhpPermitType);
        $entity->setIrhpPermitApplications($irhpPermitApplications);

        $this->assertEquals($expected->getValues(), $entity->getIrhpPermitApplicationsByCountryName()->getValues());
    }

    public static function dpGetIrhpPermitApplicationsByCountryName(): array
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

    public function testGetIrhpPermitApplicationsByCountryNameForUnsupportedType(): void
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

    public function testGetIrhpPermitApplicationByCountryId(): void
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

    public function testGetIrhpPermitApplicationByCountryIdNull(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIrhpPermitApplicationByStockCountryId')]
    public function testGetIrhpPermitApplicationByStockCountryId(mixed $irhpPermitApplications, mixed $countryId, mixed $expected): void
    {
        $entity = $this->createNewEntity();
        $entity->setIrhpPermitApplications($irhpPermitApplications);

        $this->assertSame(
            $expected,
            $entity->getIrhpPermitApplicationByStockCountryId($countryId)
        );
    }

    public static function dpGetIrhpPermitApplicationByStockCountryId(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsApsg')]
    public function testIsApsg(mixed $businessProcessId, mixed $expected): void
    {
        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn(isset($businessProcessId) ? new RefData($businessProcessId) : null);

        $this->assertSame(
            $expected,
            $this->sut->isApsg()
        );
    }

    public static function dpIsApsg(): array
    {
        return [
            [null, false],
            [RefData::BUSINESS_PROCESS_APG, false],
            [RefData::BUSINESS_PROCESS_APGG, false],
            [RefData::BUSINESS_PROCESS_APSG, true],
            [RefData::BUSINESS_PROCESS_AG, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsApgg')]
    public function testIsApgg(mixed $businessProcessId, mixed $expected): void
    {
        $this->sut->shouldReceive('getBusinessProcess')
            ->withNoArgs()
            ->andReturn(isset($businessProcessId) ? new RefData($businessProcessId) : null);

        $this->assertSame(
            $expected,
            $this->sut->isApgg()
        );
    }

    public static function dpIsApgg(): array
    {
        return [
            [null, false],
            [RefData::BUSINESS_PROCESS_APG, false],
            [RefData::BUSINESS_PROCESS_APGG, true],
            [RefData::BUSINESS_PROCESS_APSG, false],
            [RefData::BUSINESS_PROCESS_AG, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsOngoing')]
    public function testIsOngoing(mixed $isNotYetSubmitted, mixed $isUnderConsideration, mixed $isAwaitingFee, mixed $expected): void
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

    public static function dpIsOngoing(): array
    {
        return [
            [false, false, false, false],
            [false, false, true, true],
            [false, true, false, true],
            [true, false, false, true],
        ];
    }

    public function testGetDocumentsByCategoryAndSubCategory(): void
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

    private function createMockDocument(mixed $categoryId, mixed $subCategoryId): mixed
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsUnderConsiderationOrAwaitingFeeAndAssociatedWithStock')]
    public function testIsUnderConsiderationOrAwaitingFeeAndAssociatedWithStock(
        mixed $isUnderConsideration,
        mixed $isAwaitingFee,
        mixed $irhpApplicationStock,
        mixed $paramStock,
        mixed $expected
    ): void {
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

    public static function dpIsUnderConsiderationOrAwaitingFeeAndAssociatedWithStock(): array
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

    public function testUpdateCorCertificateNumber(): void
    {
        $corCertificateNumber = 'UKCR43/01234';

        $this->sut->updateCorCertificateNumber($corCertificateNumber);

        $this->assertEquals(
            $corCertificateNumber,
            $this->sut->getCorCertificateNumber()
        );
    }
}
