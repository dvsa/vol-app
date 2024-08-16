<?php

namespace Dvsa\Olcs\AcquiredRights\Client\Mapper;

use Dvsa\Olcs\AcquiredRights\Exception\MapperParseException;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use DateTimeImmutable;
use DateTimeInterface;

class ApplicationReferenceMapper
{
    protected const DATETIME_FORMAT_SUBMITTED_ON = DateTimeInterface::RFC7231;
    protected const DATETIME_FORMAT_DATE_OF_BIRTH = 'Y-m-d\TH:i:s\.v\Z';
    protected const DATETIME_FORMAT_STATUS_UPDATE_ON = DateTimeInterface::RFC7231;

    protected const APPLICATION_ID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    protected const APPLICATION_REFERENCE_REGEX = '/^[0-9a-z]{7}$/i';

    /**
     * @return ApplicationReference
     * @throws MapperParseException
     */
    public static function createFromResponseArray(array $data): ApplicationReference
    {
        return new ApplicationReference(
            ApplicationReferenceMapper::parseId($data['id'] ?? null),
            ApplicationReferenceMapper::parseReference($data['reference'] ?? null),
            ApplicationReferenceMapper::parseStatus($data['status'] ?? null),
            ApplicationReferenceMapper::parseSubmittedOn($data['submittedOn'] ?? null),
            ApplicationReferenceMapper::parseDateOfBirth($data['dateOfBirth'] ?? null),
            ApplicationReferenceMapper::parseStatusUpdateOn($data['statusUpdateOn'] ?? null)
        );
    }

    /**
     * @throws MapperParseException
     */
    protected static function parseId(?string $id): string
    {
        if (empty($id)) {
            throw new MapperParseException('Id must not be empty().');
        }

        if (preg_match(ApplicationReferenceMapper::APPLICATION_ID_REGEX, $id) !== 1) {
            throw new MapperParseException('Id is not a valid UUIDv4.');
        }

        return $id;
    }

    /**
     * @throws MapperParseException
     */
    protected static function parseReference(?string $reference): string
    {
        if (empty($reference)) {
            throw new MapperParseException('Reference must not be empty().');
        }

        if (preg_match(ApplicationReferenceMapper::APPLICATION_REFERENCE_REGEX, $reference) !== 1) {
            throw new MapperParseException('Reference is not valid.');
        }

        return $reference;
    }

    /**
     * @throws MapperParseException
     */
    protected static function parseStatus(?string $status): string
    {
        if (empty($status)) {
            throw new MapperParseException('Status must not be empty().');
        }

        if (!ApplicationReference::isValidApplicationStatus($status)) {
            throw new MapperParseException('Application Status is not valid.');
        }

        return $status;
    }

    /**
     * @throws MapperParseException
     */
    protected static function parseSubmittedOn(?string $submittedAt): DateTimeImmutable
    {
        if (empty($submittedAt)) {
            throw new MapperParseException('Submitted On must not be empty().');
        }

        $result = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT_SUBMITTED_ON, $submittedAt);
        if ($result === false) {
            throw new MapperParseException('Submitted On could not parse into DateTime from format');
        }
        return $result;
    }

    /**
     * @throws MapperParseException
     */
    protected static function parseDateOfBirth(?string $dateOfBirth): DateTimeImmutable
    {
        if (empty($dateOfBirth)) {
            throw new MapperParseException('Date of Birth must not be empty().');
        }

        $result = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT_DATE_OF_BIRTH, $dateOfBirth);
        if ($result === false) {
            throw new MapperParseException('Date of Birth could not parse into DateTime from format');
        }
        return $result;
    }

    /**
     * @throws MapperParseException
     */
    protected static function parseStatusUpdateOn(?string $statusUpdateOn = null): ?DateTimeImmutable
    {
        if (empty($statusUpdateOn)) {
            return null;
        }

        $result = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT_STATUS_UPDATE_ON, $statusUpdateOn);
        if ($result === false) {
            throw new MapperParseException('Status Update On could not parse into DateTime from format');
        }
        return $result;
    }
}
