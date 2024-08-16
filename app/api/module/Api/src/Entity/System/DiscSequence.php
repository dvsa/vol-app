<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiscSequence Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="disc_sequence",
 *    indexes={
 *        @ORM\Index(name="ix_disc_sequence_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_disc_sequence_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_disc_sequence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_disc_sequence_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class DiscSequence extends AbstractDiscSequence
{
    public const DISCS_ON_PAGE = 6;

    public const ERROR_DECREASING = 'err_decr';
    /**
     * Licence type to prefixes mapping
     *
     * @var array
     */
    protected $prefixes = [
        'ltyp_r'  => 'rPrefix', // Restricted
        'ltyp_sn' => 'snPrefix', // Standard National
        'ltyp_si' => 'siPrefix' // Standard International
    ];

    /**
     * Licence type to numbers mapping
     *
     * @var array
     */
    protected $numbers = [
        'ltyp_r'  => 'restricted',
        'ltyp_sn' => 'standardNational',
        'ltyp_si' => 'standardInternational'
    ];

    public function getDiscPrefix($licenceType)
    {
        $method = 'get' . ucfirst((string) $this->prefixes[$licenceType]);
        return $this->$method();
    }

    /**
     * @param $licenceType
     * @param $startNumber
     * @return mixed
     */
    public function setDiscStartNumber($licenceType, $startNumber)
    {
        $method = 'set' . ucfirst((string) $this->numbers[$licenceType]);
        return $this->$method($startNumber);
    }

    public function getDiscNumber($licenceType)
    {
        $method = 'get' . ucfirst((string) $this->numbers[$licenceType]);
        return $this->$method();
    }
}
