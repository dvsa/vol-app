<?php

/**
 * Custom type to turn boolean stored Y/N data back into
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Custom type to turn boolean stored Y/N data back into
 *
 * @author Michael Cooper <michael.cooper@valtech.co.uk>
 */
class YesNoType extends Type
{
    public const YESNO = 'yesno';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        unset($fieldDeclaration);
        unset($platform);

        return 'tinyint(1) NOT NULL COMMENT \'(DC2Type:yesno)\'';
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

        return $value ? 'Y' : 'N';
    }

    /**
     * Checks if the value being returned is a 1. If not it will convert 'Y' or 'YES' to 1 and anything
     * else to 0.
     * @param type $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return int
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        unset($platform);

        return (strtoupper($value) == 'Y' || strtoupper($value) == 'YES') ? 1 : 0;
    }

    public function getName()
    {
        return self::YESNO;
    }
}
