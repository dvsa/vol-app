<?php

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LegacyPiReason Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="legacy_pi_reason",
 *    indexes={
 *        @ORM\Index(name="ix_legacy_pi_reason_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_legacy_pi_reason_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLegacyPiReason implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=false)
     */
    protected $description;

    /**
     * Goods or psv
     *
     * @var string
     *
     * @ORM\Column(type="string", name="goods_or_psv", length=3, nullable=false)
     */
    protected $goodsOrPsv;

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
     * Is decision
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_decision", nullable=false)
     */
    protected $isDecision;

    /**
     * Is ni
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_ni", nullable=false)
     */
    protected $isNi;

    /**
     * Is read only
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_read_only", nullable=false)
     */
    protected $isReadOnly;

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
     * Section code
     *
     * @var string
     *
     * @ORM\Column(type="string", name="section_code", length=50, nullable=false)
     */
    protected $sectionCode;

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
     * @return LegacyPiReason
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return LegacyPiReason
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the goods or psv
     *
     * @param string $goodsOrPsv new value being set
     *
     * @return LegacyPiReason
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return string
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return LegacyPiReason
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
     * Set the is decision
     *
     * @param boolean $isDecision new value being set
     *
     * @return LegacyPiReason
     */
    public function setIsDecision($isDecision)
    {
        $this->isDecision = $isDecision;

        return $this;
    }

    /**
     * Get the is decision
     *
     * @return boolean
     */
    public function getIsDecision()
    {
        return $this->isDecision;
    }

    /**
     * Set the is ni
     *
     * @param boolean $isNi new value being set
     *
     * @return LegacyPiReason
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return boolean
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the is read only
     *
     * @param boolean $isReadOnly new value being set
     *
     * @return LegacyPiReason
     */
    public function setIsReadOnly($isReadOnly)
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    /**
     * Get the is read only
     *
     * @return boolean
     */
    public function getIsReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return LegacyPiReason
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
     * Set the section code
     *
     * @param string $sectionCode new value being set
     *
     * @return LegacyPiReason
     */
    public function setSectionCode($sectionCode)
    {
        $this->sectionCode = $sectionCode;

        return $this;
    }

    /**
     * Get the section code
     *
     * @return string
     */
    public function getSectionCode()
    {
        return $this->sectionCode;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LegacyPiReason
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
