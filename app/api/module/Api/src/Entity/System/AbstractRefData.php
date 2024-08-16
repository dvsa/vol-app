<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RefData Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="ref_data",
 *    indexes={
 *        @ORM\Index(name="ix_ref_data_parent_id", columns={"parent_id"}),
 *        @ORM\Index(name="ix_ref_data_ref_data_category_id", columns={"ref_data_category_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_ref_data_ref_data_category_id_olbs_key",
     *     columns={"ref_data_category_id","olbs_key"})
 *    }
 * )
 */
abstract class AbstractRefData implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=512, nullable=true)
     * @Gedmo\Translatable
     */
    protected $description;

    /**
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="display_order", nullable=true)
     */
    protected $displayOrder;

    /**
     * Identifier - Id
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id", length=32)
     */
    protected $id;

    /**
     * Olbs key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_key", length=20, nullable=true)
     */
    protected $olbsKey;

    /**
     * Parent
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * Ref data category id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="ref_data_category_id", length=32, nullable=false)
     */
    protected $refDataCategoryId;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return RefData
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
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return RefData
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * Get the display order
     *
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Set the id
     *
     * @param string $id new value being set
     *
     * @return RefData
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the olbs key
     *
     * @param string $olbsKey new value being set
     *
     * @return RefData
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return string
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the parent
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $parent entity being set as the value
     *
     * @return RefData
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get the parent
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the ref data category id
     *
     * @param string $refDataCategoryId new value being set
     *
     * @return RefData
     */
    public function setRefDataCategoryId($refDataCategoryId)
    {
        $this->refDataCategoryId = $refDataCategoryId;

        return $this;
    }

    /**
     * Get the ref data category id
     *
     * @return string
     */
    public function getRefDataCategoryId()
    {
        return $this->refDataCategoryId;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return RefData
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
