<?php

namespace Dvsa\Olcs\Api\Entity\Prohibition;

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
 * ProhibitionDefect Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="prohibition_defect",
 *    indexes={
 *        @ORM\Index(name="ix_prohibition_defect_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_prohibition_defect_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_prohibition_defect_prohibition_id", columns={"prohibition_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_prohibition_defect_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractProhibitionDefect implements BundleSerializableInterface, JsonSerializable
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
     * Defect type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="defect_type", length=255, nullable=true)
     */
    protected $defectType;

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
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=4000, nullable=true)
     */
    protected $notes;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Prohibition
     *
     * @var \Dvsa\Olcs\Api\Entity\Prohibition\Prohibition
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Prohibition\Prohibition",
     *     fetch="LAZY",
     *     inversedBy="defects"
     * )
     * @ORM\JoinColumn(name="prohibition_id", referencedColumnName="id", nullable=false)
     */
    protected $prohibition;

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
     * @return ProhibitionDefect
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
     * Set the defect type
     *
     * @param string $defectType new value being set
     *
     * @return ProhibitionDefect
     */
    public function setDefectType($defectType)
    {
        $this->defectType = $defectType;

        return $this;
    }

    /**
     * Get the defect type
     *
     * @return string
     */
    public function getDefectType()
    {
        return $this->defectType;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ProhibitionDefect
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return ProhibitionDefect
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
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return ProhibitionDefect
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return ProhibitionDefect
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
     * Set the prohibition
     *
     * @param \Dvsa\Olcs\Api\Entity\Prohibition\Prohibition $prohibition entity being set as the value
     *
     * @return ProhibitionDefect
     */
    public function setProhibition($prohibition)
    {
        $this->prohibition = $prohibition;

        return $this;
    }

    /**
     * Get the prohibition
     *
     * @return \Dvsa\Olcs\Api\Entity\Prohibition\Prohibition
     */
    public function getProhibition()
    {
        return $this->prohibition;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ProhibitionDefect
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
