<?php

namespace Dvsa\Olcs\AcquiredRights\Exception\Mapper;

use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsException;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsExpiredException;
use Dvsa\Olcs\AcquiredRights\Exception\AcquiredRightsNotApprovedException;
use Dvsa\Olcs\AcquiredRights\Exception\DateOfBirthMismatchException;
use Dvsa\Olcs\AcquiredRights\Exception\MapperParseException;
use Dvsa\Olcs\AcquiredRights\Exception\ReferenceNotFoundException;
use Dvsa\Olcs\AcquiredRights\Exception\ServiceException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

class AcquiredRightsExceptionToValidationExceptionMapper
{
    protected const ERROR_AR_REF_NUMBER_NOT_FOUND = 'lva.tm-details-details.error.ar-ref-number-not-found';
    protected const ERROR_AR_DATE_OF_BIRTH_MISMATCH = 'lva.tm-details-details.error.ar-date-of-birth-mismatch';
    protected const ERROR_AR_API_FAILURE = 'lva.tm-details-details.error.ar-api-failure';
    protected const ERROR_AR_NOT_APPROVED = 'lva.tm-details-details.error.ar-not-approved';
    protected const ERROR_AR_EXPIRED = 'lva.tm-details-details.error.ar-expired';

    public const MAP = [
        ReferenceNotFoundException::class => [
            'ar_not_found' => self::ERROR_AR_REF_NUMBER_NOT_FOUND,
        ],
        DateOfBirthMismatchException::class => [
            'ar_dob_mismatch' => self::ERROR_AR_DATE_OF_BIRTH_MISMATCH,
        ],
        AcquiredRightsNotApprovedException::class => [
            'ar_not_approved' => self::ERROR_AR_NOT_APPROVED,
        ],
        AcquiredRightsExpiredException::class => [
            'ar_expired' => self::ERROR_AR_EXPIRED,
        ],
        ServiceException::class => [
            'ar_api_failure' => self::ERROR_AR_API_FAILURE,
        ],
        MapperParseException::class => [
            'ar_api_failure' => self::ERROR_AR_API_FAILURE,
        ],
    ];

    /**
     * @throws AcquiredRightsException
     * @throws ValidationException
     */
    public static function mapException(AcquiredRightsException $exception, string $inputName, bool $rethrowIfNotExists = true): bool
    {
        $exceptionClassName = $exception::class;

        if (!array_key_exists($exceptionClassName, self::MAP)) {
            if ($rethrowIfNotExists) {
                throw $exception;
            }
            return false;
        }

        throw new ValidationException([
            $inputName => [
                array_keys(self::MAP[$exceptionClassName])[0] => array_values(self::MAP[$exceptionClassName])[0]
            ]
        ]);
    }
}
