<?php

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Custom date type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateType extends Type
{
    /**
     * Convert to PHP Value
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return string|null
     * @throws InvalidFormat
     * @inheritdoc
     */
    #[\Override]
    public function convertToPHPValue(
        mixed $value,
        AbstractPlatform $platform
    ): mixed {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        $val = \DateTime::createFromFormat('!Y-m-d', $value);
        if ($val instanceof \DateTime) {
            return $val->format('Y-m-d');
        }

        throw InvalidFormat::new(
            $value,
            'date',
            'Y-m-d'
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
    public function convertToDatabaseValue(
        mixed $value,
        AbstractPlatform $platform
    ): mixed {
        if ($value !== null && !($value instanceof \DateTime)) {
            $value = new DateTime($value);
        }

        return ($value !== null)
            ? $value->format('Y-m-d') : null;
    }

    #[\Override]
    public function getSQLDeclaration(
        array $column,
        AbstractPlatform $platform
    ): string {
        return $platform->getDateTypeDeclarationSQL($column);
    }
}
