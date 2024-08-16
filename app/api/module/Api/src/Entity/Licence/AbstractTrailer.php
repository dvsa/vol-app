<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Trailer Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="trailer",
 *    indexes={
 *        @ORM\Index(name="ix_trailer_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_trailer_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_trailer_licence_id", columns={"licence_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_trailer_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTrailer implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * Is longer semi trailer
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="is_longer_semi_trailer",
     *     nullable=false,
     *     options={"default": 0})
     */
    protected $isLongerSemiTrailer = 0;

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
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=false)
     */
    protected $licence;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Specified date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="specified_date", nullable=true)
     */
    protected $specifiedDate;

    /**
     * Trailer no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="trailer_no", length=20, nullable=false)
     */
    protected $trailerNo;

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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Trailer
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Trailer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the is longer semi trailer
     *
     * @param boolean $isLongerSemiTrailer new value being set
     *
     * @return Trailer
     */
    public function setIsLongerSemiTrailer($isLongerSemiTrailer)
    {
        $this->isLongerSemiTrailer = $isLongerSemiTrailer;

        return $this;
    }

    /**
     * Get the is longer semi trailer
     *
     * @return boolean
     */
    public function getIsLongerSemiTrailer()
    {
        return $this->isLongerSemiTrailer;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Trailer
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return Trailer
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Trailer
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the specified date
     *
     * @param \DateTime $specifiedDate new value being set
     *
     * @return Trailer
     */
    public function setSpecifiedDate($specifiedDate)
    {
        $this->specifiedDate = $specifiedDate;

        return $this;
    }

    /**
     * Get the specified date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getSpecifiedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->specifiedDate);
        }

        return $this->specifiedDate;
    }

    /**
     * Set the trailer no
     *
     * @param string $trailerNo new value being set
     *
     * @return Trailer
     */
    public function setTrailerNo($trailerNo)
    {
        $this->trailerNo = $trailerNo;

        return $this;
    }

    /**
     * Get the trailer no
     *
     * @return string
     */
    public function getTrailerNo()
    {
        return $this->trailerNo;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Trailer
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
