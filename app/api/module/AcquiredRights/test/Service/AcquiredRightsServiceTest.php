<?php

namespace Dvsa\Olcs\AcquiredRights\Client;

use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsException;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsExpiredException;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsNotApprovedException;
use Dvsa\Olcs\AcquiredRights\Exception\DateOfBirthMismatchException;
use Dvsa\Olcs\AcquiredRights\Exception\ReferenceNotFoundException;
use Dvsa\Olcs\AcquiredRights\Exception\SoftExceptionInterface;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use Dvsa\Olcs\AcquiredRights\Service\AcquiredRightsService;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Laminas\Log\LoggerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AcquiredRightsServiceTest extends MockeryTestCase
{
    protected $sut;
    protected $loggerInterfaceMock;
    protected $acquiredRightsClientMock;

    public function setUp(): void
    {
        $this->loggerInterfaceMock = m::mock(LoggerInterface::class)->shouldIgnoreMissing()->byDefault();

        $this->acquiredRightsClientMock = m::mock(AcquiredRightsClient::class);
        $this->acquiredRightsClientMock
            ->expects('fetchByReference')
            ->with('ABC1234')
            ->once()
            ->andReturn(new ApplicationReference(
                '6fcf9551-ade4-4b48-b078-6db59559a182',
                'ABC1234',
                ApplicationReference::APPLICATION_STATUS_APPROVED,
                \DateTimeImmutable::createFromMutable((new \DateTime())->sub(new \DateInterval('P10D'))),
                \DateTimeImmutable::createFromFormat('j-M-Y', '01-Feb-2000')
            ))
            ->byDefault();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceValidThrowsNoExceptions()
    {
        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '01-Feb-2000');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceExpiryElapsedThrowsAcquiredRightsExpiredException()
    {
        $this->expectException(AcquiredRightsExpiredException::class);

        $this->acquiredRightsClientMock->shouldNotReceive('fetchByReference');

        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->sub(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '01-Feb-2000');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceDateOfBirthMismatchThrowsDateOfBirthMismatchException()
    {
        $this->expectException(DateOfBirthMismatchException::class);

        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '19-Feb-1923');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderVerifyAcquiredRightsByReferenceApplicationNotApproved')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceApplicationNotApprovedThrowsAcquiredRightsNotApprovedException(string $status, bool $shouldThrow)
    {
        if ($shouldThrow) {
            $this->expectException(AcquiredRightsNotApprovedException::class);
        }

        $this->acquiredRightsClientMock->expects('fetchByReference')->with('ABC1234')->once()->andReturn(new ApplicationReference(
            '6fcf9551-ade4-4b48-b078-6db59559a182',
            'ABC1234',
            $status,
            \DateTimeImmutable::createFromMutable((new \DateTime())->sub(new \DateInterval('P10D'))),
            \DateTimeImmutable::createFromFormat('j-M-Y', '01-Feb-2000')
        ));

        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '01-Feb-2000');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth);
    }

    public static function dataProviderVerifyAcquiredRightsByReferenceApplicationNotApproved(): array
    {
        return [
            ApplicationReference::APPLICATION_STATUS_SUBMITTED => [
                ApplicationReference::APPLICATION_STATUS_SUBMITTED,
                true,
            ],
            ApplicationReference::APPLICATION_STATUS_UNDER_CONSIDERATION => [
                ApplicationReference::APPLICATION_STATUS_UNDER_CONSIDERATION,
                true,
            ],
            ApplicationReference::APPLICATION_STATUS_APPROVED => [
                ApplicationReference::APPLICATION_STATUS_APPROVED,
                false,
            ],
            ApplicationReference::APPLICATION_STATUS_DECLINED => [
                ApplicationReference::APPLICATION_STATUS_DECLINED,
                true,
            ],
            ApplicationReference::APPLICATION_STATUS_APPROVED_AFTER_APPEAL => [
                ApplicationReference::APPLICATION_STATUS_APPROVED_AFTER_APPEAL,
                false,
            ],
            ApplicationReference::APPLICATION_STATUS_DECLINED_AFTER_APPEAL => [
                ApplicationReference::APPLICATION_STATUS_DECLINED_AFTER_APPEAL,
                true,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceThrowsSoftExceptionAreLoggedAsInfoAndRethrown()
    {
        $exception = new class () extends AcquiredRightsException implements SoftExceptionInterface {
        };
        $this->acquiredRightsClientMock->expects('fetchByReference')->with('ABC1234')->once()->andThrow($exception);

        $this->expectExceptionObject($exception);

        $this->loggerInterfaceMock->expects('info');

        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '19-Feb-1923');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceThrowsOtherExceptionsAreLoggedAsErrAndRethrown()
    {
        $exception = new class () extends AcquiredRightsException {
        };
        $this->acquiredRightsClientMock->expects('fetchByReference')->with('ABC1234')->once()->andThrow($exception);

        $this->expectExceptionObject($exception);

        $this->loggerInterfaceMock->expects('err');

        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '19-Feb-1923');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function verifyAcquiredRightsByReferenceThrowsWhenInputFieldDefinedValidationErrorIsRethrown()
    {
        $exception = new ReferenceNotFoundException();
        $this->acquiredRightsClientMock->expects('fetchByReference')->with('ABC1234')->once()->andThrow($exception);

        $this->expectException(ValidationException::class);

        $this->sut = new AcquiredRightsService(
            $this->loggerInterfaceMock,
            $this->acquiredRightsClientMock,
            \DateTimeImmutable::createFromMutable((new \DateTime())->add(new \DateInterval('P10D'))),
            true
        );

        $dateOfBirth = \DateTimeImmutable::createFromFormat('j-M-Y', '19-Feb-1923');
        $this->sut->verifyAcquiredRightsByReference('ABC1234', $dateOfBirth, 'someFieldName');
    }
}
