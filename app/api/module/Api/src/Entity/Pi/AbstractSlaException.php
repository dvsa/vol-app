<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SlaException Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sla_exception",
 *    indexes={
 *        @ORM\Index(name="ix_sla_exception_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_sla_exception_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractSlaException implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * SLA Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sla_description", length=255, nullable=false)
     */
    protected $slaDescription;

    /**
     * SLA Exception Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sla_exception_description", length=255, nullable=false)
     */
    protected $slaExceptionDescription;

    /**
     * Effective From
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_from", nullable=false)
     */
    protected $effectiveFrom;

    /**
     * Effective To
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_to", nullable=true)
     */
    protected $effectiveTo;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Set the sla description
     *
     * @param string $slaDescription new value being set
     *
     * @return AbstractSlaException
     */
    public function setSlaDescription($slaDescription)
    {
        $this->slaDescription = $slaDescription;

        return $this;
    }

    /**
     * Get the sla description
     *
     * @return string
     */
    public function getSlaDescription()
    {
        return $this->slaDescription;
    }

    /**
     * Set the sla exception description
     *
     * @param string $slaExceptionDescription new value being set
     *
     * @return AbstractSlaException
     */
    public function setSlaExceptionDescription($slaExceptionDescription)
    {
        $this->slaExceptionDescription = $slaExceptionDescription;

        return $this;
    }

    /**
     * Get the sla exception description
     *
     * @return string
     */
    public function getSlaExceptionDescription()
    {
        return $this->slaExceptionDescription;
    }

    /**
     * Set the effective from date
     *
     * @param \DateTime $effectiveFrom new value being set
     *
     * @return AbstractSlaException
     */
    public function setEffectiveFrom($effectiveFrom)
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    /**
     * Get the effective from date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string
     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
    }

    /**
     * Set the effective to date
     *
     * @param \DateTime $effectiveTo new value being set
     *
     * @return AbstractSlaException
     */
    public function setEffectiveTo($effectiveTo)
    {
        $this->effectiveTo = $effectiveTo;

        return $this;
    }

    /**
     * Get the effective to date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string
     */
    public function getEffectiveTo($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveTo);
        }

        return $this->effectiveTo;
    }

}
