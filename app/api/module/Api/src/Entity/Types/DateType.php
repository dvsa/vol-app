<?php

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateType as DoctrineDateType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Custom date type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateType extends DoctrineDateType
{
    /**
     * Convert to PHP Value
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return string|null
     * @throws ConversionException
     * @inheritdoc
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        $val = \DateTime::createFromFormat('!' . $platform->getDateFormatString(), $value);
        if ($val instanceof \DateTime) {
            return $val->format('Y-m-d');
        }

        throw ConversionException::conversionFailedFormat(
            $value,
            $this->getName(),
            $platform->getDateFormatString()
        );
    }

    /**
     * Convert to Database value
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return null|string
     * @inheritdoc
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value !== null && !($value instanceof \DateTime)) {
            $value = new DateTime($value);
        }

        return ($value !== null)
            ? $value->format($platform->getDateFormatString()) : null;
    }
}
