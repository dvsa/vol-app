<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractOperatingCentreOpposition Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="operating_centre_opposition",
 *    indexes={
 *        @ORM\Index(name="ix_operating_centre_opposition_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_operating_centre_opposition_opposition_id", columns={"opposition_id"}),
 *        @ORM\Index(name="uk_operating_centre_opposition_olbs_oc_id_olbs_opp_id_olbs_type", columns={"olbs_oc_id", "olbs_opp_id", "olbs_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_operating_centre_opposition_olbs_oc_id_olbs_opp_id_olbs_type", columns={"olbs_oc_id", "olbs_opp_id", "olbs_type"})
 *    }
 * )
 */
abstract class AbstractOperatingCentreOpposition implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="opposition_id", nullable=false)
     */
    protected $opposition_id = 0;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="operating_centre_id", nullable=false)
     */
    protected $operating_centre_id = 0;

    /**
     * Olbs oc id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_oc_id", nullable=true)
     */
    protected $olbsOcId;

    /**
     * Olbs opp id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_opp_id", nullable=true)
     */
    protected $olbsOppId;

    /**
     * used to differntiate source of data during ETL when one OLCS table relates to many OLBS. Can be dropped when fully live
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;


    /**
     * Set the opposition_id
     *
     * @param int $opposition_id new value being set
     *
     * @return OperatingCentreOpposition
     */
    public function setOpposition_id($opposition_id)
    {
        $this->opposition_id = $opposition_id;

        return $this;
    }

    /**
     * Get the opposition_id
     *
     * @return int     */
    public function getOpposition_id()
    {
        return $this->opposition_id;
    }

    /**
     * Set the operating_centre_id
     *
     * @param int $operating_centre_id new value being set
     *
     * @return OperatingCentreOpposition
     */
    public function setOperating_centre_id($operating_centre_id)
    {
        $this->operating_centre_id = $operating_centre_id;

        return $this;
    }

    /**
     * Get the operating_centre_id
     *
     * @return int     */
    public function getOperating_centre_id()
    {
        return $this->operating_centre_id;
    }

    /**
     * Set the olbs oc id
     *
     * @param int $olbsOcId new value being set
     *
     * @return OperatingCentreOpposition
     */
    public function setOlbsOcId($olbsOcId)
    {
        $this->olbsOcId = $olbsOcId;

        return $this;
    }

    /**
     * Get the olbs oc id
     *
     * @return int     */
    public function getOlbsOcId()
    {
        return $this->olbsOcId;
    }

    /**
     * Set the olbs opp id
     *
     * @param int $olbsOppId new value being set
     *
     * @return OperatingCentreOpposition
     */
    public function setOlbsOppId($olbsOppId)
    {
        $this->olbsOppId = $olbsOppId;

        return $this;
    }

    /**
     * Get the olbs opp id
     *
     * @return int     */
    public function getOlbsOppId()
    {
        return $this->olbsOppId;
    }

    /**
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return OperatingCentreOpposition
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
