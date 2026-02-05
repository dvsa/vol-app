<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractSlaException Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sla_exception",
 *    indexes={
 *        @ORM\Index(name="created_by", columns={"created_by"}),
 *        @ORM\Index(name="last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractSlaException implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
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
     * Sla description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sla_description", length=255, nullable=false)
     */
    protected $slaDescription = '';

    /**
     * Sla exception description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sla_exception_description", length=255, nullable=false)
     */
    protected $slaExceptionDescription = '';

    /**
     * Effective from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_from", nullable=false)
     */
    protected $effectiveFrom;

    /**
     * Effective to
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_to", nullable=true)
     */
    protected $effectiveTo;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=true, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SlaException
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return SlaException
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return SlaException
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the sla description
     *
     * @param string $slaDescription new value being set
     *
     * @return SlaException
     */
    public function setSlaDescription($slaDescription)
    {
        $this->slaDescription = $slaDescription;

        return $this;
    }

    /**
     * Get the sla description
     *
     * @return string     */
    public function getSlaDescription()
    {
        return $this->slaDescription;
    }

    /**
     * Set the sla exception description
     *
     * @param string $slaExceptionDescription new value being set
     *
     * @return SlaException
     */
    public function setSlaExceptionDescription($slaExceptionDescription)
    {
        $this->slaExceptionDescription = $slaExceptionDescription;

        return $this;
    }

    /**
     * Get the sla exception description
     *
     * @return string     */
    public function getSlaExceptionDescription()
    {
        return $this->slaExceptionDescription;
    }

    /**
     * Set the effective from
     *
     * @param \DateTime $effectiveFrom new value being set
     *
     * @return SlaException
     */
    public function setEffectiveFrom($effectiveFrom)
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    /**
     * Get the effective from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
    }

    /**
     * Set the effective to
     *
     * @param \DateTime $effectiveTo new value being set
     *
     * @return SlaException
     */
    public function setEffectiveTo($effectiveTo)
    {
        $this->effectiveTo = $effectiveTo;

        return $this;
    }

    /**
     * Get the effective to
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEffectiveTo($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveTo);
        }

        return $this->effectiveTo;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SlaException
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
