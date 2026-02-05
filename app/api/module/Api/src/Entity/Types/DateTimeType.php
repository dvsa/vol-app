<?php

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType as DoctrineDateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Custom date time type
 *
 * Make sure all times are stored in the DB as UTC
 */
class DateTimeType extends DoctrineDateTimeType
{
    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed            $value    Value to be converted
     * @param AbstractPlatform $platform Platform eg MySQL
     *
     * @return \DateTime|string|null
     * @throws ConversionException
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return $value->format(\DateTime::ISO8601);
        }

        // create from format, using timezone
        $val = \DateTime::createFromFormat($platform->getDateTimeFormatString(), $value, $this->getDbTimeZone());
        if (!$val) {
            // create date, using timezone
            $val = date_create($value, $this->getDbTimeZone());
        }

        if ($val instanceof \DateTime) {
            $val->setTimezone($this->getApplicationTimeZone());
            return $val->format(\DateTime::ISO8601);
        }

        throw ConversionException::conversionFailedFormat(
            $value,
            $this->getName(),
            $platform->getDateTimeFormatString()
        );
    }

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed            $value    Value to be converted
     * @param AbstractPlatform $platform Platform eg MySQL
     *
     * @return null|string
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!($value instanceof \DateTime)) {
            $value = new DateTime($value);
        }

        // Set timezone, so that all times are stored in UTC
        $value->setTimezone($this->getDbTimeZone());
        return $value->format($platform->getDateTimeFormatString());
    }

    /**
     * Get the timezone that dates are stored in
     *
     * @return \DateTimeZone
     */
    private function getDbTimeZone()
    {
        return new \DateTimeZone('UTC');
    }

    /**
     * Get the timezoe that the application is running in
     *
     * @return \DateTimeZone
     */
    private function getApplicationTimeZone()
    {
        return new \DateTimeZone(date_default_timezone_get());
    }
}
