<?php

/**
 * Custom type to turn boolean stored Y/N data back into (with null allowance)
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Custom type to turn boolean stored Y/N data back into (with null allowance)
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class YesNoNullType extends Type
{
    public const YESNONULL = 'yesnonull';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        unset($fieldDeclaration);
        unset($platform);

        return 'tinyint(1) NULL COMMENT \'(DC2Type:yesnonull)\'';
    }

    /**
     * Converts database value to php one.
     *
     * @param boolean $value
     * @param AbstractPlatform $platform
     * @return null|string
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        unset($platform);

        if ($value === null) {
            return null;
        }
        return $value ? 'Y' : 'N';
    }

    /**
     * converts non null values to its boolean representation: Y,Yes => 1, anything else => 0
     *
     * @param type $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return int
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        unset($platform);

        if ($value === null) {
            return null;
        }
        return (strtoupper($value) == 'Y' || strtoupper($value) == 'YES') ? 1 : 0;
    }

    /**
     * Returns type name
     *
     * @return string
     */
    public function getName()
    {
        return self::YESNONULL;
    }
}
