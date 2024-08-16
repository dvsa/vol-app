<?php

namespace Dvsa\Olcs\Api\Entity\ContactDetails;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="country",
 *    indexes={
 *        @ORM\Index(name="ix_country_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_country_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class Country extends AbstractCountry
{
    public const ID_AUSTRIA = 'AT';
    public const ID_BELARUS = 'BY';
    public const ID_BELGIUM = 'BE';
    public const ID_BULGARIA = 'BG';
    public const ID_CROATIA = 'HR';
    public const ID_CYPRUS = 'CY';
    public const ID_CZECH_REPUBLIC = 'CZ';
    public const ID_DENMARK = 'DK';
    public const ID_ESTONIA = 'EE';
    public const ID_FINLAND = 'FI';
    public const ID_FRANCE = 'FR';
    public const ID_GEORGIA = 'GE';
    public const ID_GERMANY = 'DE';
    public const ID_GREECE = 'GR';
    public const ID_HUNGARY = 'HU';
    public const ID_ICELAND = 'IS';
    public const ID_IRELAND = 'IE';
    public const ID_ITALY = 'IT';
    public const ID_KAZAKHSTAN = 'KZ';
    public const ID_LATVIA = 'LV';
    public const ID_LIECHTENSTEIN = 'LI';
    public const ID_LITHUANIA = 'LT';
    public const ID_LUXEMBOURG = 'LU';
    public const ID_MALTA = 'MT';
    public const ID_MOROCCO = 'MA';
    public const ID_NETHERLANDS = 'NL';
    public const ID_NORWAY = 'NO';
    public const ID_POLAND = 'PL';
    public const ID_PORTUGAL = 'PT';
    public const ID_ROMANIA = 'RO';
    public const ID_RUSSIA = 'RU';
    public const ID_SLOVAKIA = 'SK';
    public const ID_SLOVENIA = 'SI';
    public const ID_SPAIN = 'ES';
    public const ID_SWEDEN = 'SE';
    public const ID_TUNISIA = 'TN';
    public const ID_TURKEY = 'TR';
    public const ID_UKRAINE = 'UA';
    public const ID_UNITED_KINGDOM = 'GB';

    /**
     * Whether this country is Morocco
     *
     * @return bool
     */
    public function isMorocco()
    {
        return $this->id == self::ID_MOROCCO;
    }
}
