<?php

namespace Dvsa\Olcs\AcquiredRights\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Dvsa\Olcs\AcquiredRights\Client\AcquiredRightsClient;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsException;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsExpiredException;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsNotApprovedException;
use Dvsa\Olcs\AcquiredRights\Exception\DateOfBirthMismatchException;
use Dvsa\Olcs\AcquiredRights\Exception\Mapper\AcquiredRightsExceptionToValidationExceptionMapper;
use Dvsa\Olcs\AcquiredRights\Exception\SoftExceptionInterface;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use Laminas\Log\LoggerInterface;

class AcquiredRightsService
{
    public function __construct(protected LoggerInterface $logger, protected AcquiredRightsClient $acquiredRightsClient, protected DateTimeImmutable $acquiredRightsExpiry, protected bool $enableCheck = false)
    {
    }

    /**
     * Verifies the application reference number provided.
     *
     * This function will check for expiry, call the Acquired Rights API, and validate the response by:
     *  - ensures the reference number exists
     *  - ensures the date of birth provided matches the application record
     *  - ensures the application has been APPROVED or APPROVED_AFTER_APPEAL
     *
     * If $inputFieldName is provided, a ValidationException is thrown if an error occurs;
     * else original exceptions are thrown.
     *
     * @throws AcquiredRightsException
     * @throws AcquiredRightsExpiredException
     * @throws AcquiredRightsNotApprovedException
     * @throws DateOfBirthMismatchException
     * @throws \Dvsa\Olcs\AcquiredRights\Exception\MapperParseException
     * @throws \Dvsa\Olcs\AcquiredRights\Exception\ReferenceNotFoundException
     * @throws \Dvsa\Olcs\AcquiredRights\Exception\ServiceException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     */
    public function verifyAcquiredRightsByReference(string $reference, DateTimeImmutable $dateOfBirth, string $inputFieldName = null): ApplicationReference
    {
        try {
            $this->checkAcquiredRightsExpiry();
            $applicationReference = $this->fetchApplicationByReference($reference);
            $this->checkDateOfBirthMatch($applicationReference, $dateOfBirth);
            $this->checkApplicationApproved($applicationReference);
            return $applicationReference;
        } catch (AcquiredRightsException $exception) {
            if ($exception instanceof SoftExceptionInterface) {
                $this->logger->info(
                    sprintf(
                        'Acquired Rights Service: There was an error with the reference number (%s) provided',
                        $reference
                    ),
                    [$exception]
                );
            } else {
                $this->logger->err(
                    sprintf(
                        'Acquired Rights Service: There was an error when fetching the acquired rights record (%s)',
                        $reference
                    ),
                    [$exception]
                );
            }
            if (!empty($inputFieldName)) {
                AcquiredRightsExceptionToValidationExceptionMapper::mapException($exception, $inputFieldName);
            }
            throw $exception;
        }
    }

    /**
     * @throws \Dvsa\Olcs\AcquiredRights\Exception\MapperParseException
     * @throws \Dvsa\Olcs\AcquiredRights\Exception\ReferenceNotFoundException
     * @throws \Dvsa\Olcs\AcquiredRights\Exception\ServiceException
     */
    public function fetchApplicationByReference(string $reference): ApplicationReference
    {
        return $this->acquiredRightsClient->fetchByReference($reference);
    }

    public function isCheckEnabled(): bool
    {
        return $this->enableCheck;
    }

    public function isAcquiredRightsExpired(): bool
    {
        return (new DateTimeImmutable() > $this->acquiredRightsExpiry);
    }

    /**
     * @throws AcquiredRightsNotApprovedException
     */
    protected function checkApplicationApproved(ApplicationReference $applicationReference): void
    {
        if (
            !in_array(
                $applicationReference->getStatus(),
                [
                ApplicationReference::APPLICATION_STATUS_APPROVED,
                ApplicationReference::APPLICATION_STATUS_APPROVED_AFTER_APPEAL,
                ]
            )
        ) {
            throw new AcquiredRightsNotApprovedException(
                sprintf(
                    'The acquired rights application referenced (%s) has not been approved (%s).',
                    $applicationReference->getReference(),
                    $applicationReference->getStatus()
                )
            );
        }
    }

    /**
     * @throws DateOfBirthMismatchException
     */
    protected function checkDateOfBirthMatch(ApplicationReference $applicationReference, DateTimeImmutable $dateOfBirth): void
    {
        if ($applicationReference->getDateOfBirth()->diff($dateOfBirth)->d !== 0) {
            throw new DateOfBirthMismatchException(
                sprintf(
                    'The date of birth the user entered (%s), does not match the application reference (%s) summary returned from acquired rights (%s).',
                    $dateOfBirth->format('F j, Y'),
                    $applicationReference->getReference(),
                    $applicationReference->getDateOfBirth()->format('F j, Y')
                )
            );
        }
    }

    /**
     * @throws AcquiredRightsExpiredException
     */
    protected function checkAcquiredRightsExpiry(): void
    {
        if ($this->isAcquiredRightsExpired()) {
            throw new AcquiredRightsExpiredException(
                sprintf(
                    'The ability to use acquired rights has expired. Expired %s',
                    $this->acquiredRightsExpiry->format(DateTimeInterface::RFC7231)
                )
            );
        }
    }
}
