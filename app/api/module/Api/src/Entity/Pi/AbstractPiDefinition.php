<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

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
 * PiDefinition Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="pi_definition",
 *    indexes={
 *        @ORM\Index(name="ix_pi_definition_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_pi_definition_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_pi_definition_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractPiDefinition implements BundleSerializableInterface, JsonSerializable
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
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
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
     * Is ni
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_ni", nullable=false)
     */
    protected $isNi;

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
     * Pi definition category
     *
     * @var string
     *
     * @ORM\Column(type="string", name="pi_definition_category", length=32, nullable=false)
     */
    protected $piDefinitionCategory;

    /**
     * Section code
     *
     * @var string
     *
     * @ORM\Column(type="string", name="section_code", length=20, nullable=false)
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
     * @return PiDefinition
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
     * @return PiDefinition
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
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv entity being set as the value
     *
     * @return PiDefinition
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
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
     * @return PiDefinition
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
     * Set the is ni
     *
     * @param string $isNi new value being set
     *
     * @return PiDefinition
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return string
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return PiDefinition
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
     * Set the pi definition category
     *
     * @param string $piDefinitionCategory new value being set
     *
     * @return PiDefinition
     */
    public function setPiDefinitionCategory($piDefinitionCategory)
    {
        $this->piDefinitionCategory = $piDefinitionCategory;

        return $this;
    }

    /**
     * Get the pi definition category
     *
     * @return string
     */
    public function getPiDefinitionCategory()
    {
        return $this->piDefinitionCategory;
    }

    /**
     * Set the section code
     *
     * @param string $sectionCode new value being set
     *
     * @return PiDefinition
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
     * @return PiDefinition
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
