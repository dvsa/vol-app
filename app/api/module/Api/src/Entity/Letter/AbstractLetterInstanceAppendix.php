<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

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
 * LetterInstanceAppendix Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_instance_appendix",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_appendix_letter_appendix_version_id", columns={"letter_appendix_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLetterInstanceAppendix implements BundleSerializableInterface, JsonSerializable
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
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false)
     */
    protected $displayOrder;

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
     * Letter appendix version
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_appendix_version_id", referencedColumnName="id", nullable=false)
     */
    protected $letterAppendixVersion;

    /**
     * Letter instance
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstance
     *
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstance",
     *     fetch="LAZY",
     *     inversedBy="letterInstanceAppendices"
     * )
     * @ORM\JoinColumn(name="letter_instance_id", referencedColumnName="id", nullable=false)
     */
    protected $letterInstance;

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return self
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
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return self
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return self
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
     * Set the letter appendix version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion $letterAppendixVersion entity being set as the value
     *
     * @return self
     */
    public function setLetterAppendixVersion($letterAppendixVersion)
    {
        $this->letterAppendixVersion = $letterAppendixVersion;

        return $this;
    }

    /**
     * Get the letter appendix version
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion
     */
    public function getLetterAppendixVersion()
    {
        return $this->letterAppendixVersion;
    }

    /**
     * Set the letter instance
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterInstance $letterInstance entity being set as the value
     *
     * @return self
     */
    public function setLetterInstance($letterInstance)
    {
        $this->letterInstance = $letterInstance;

        return $this;
    }

    /**
     * Get the letter instance
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterInstance
     */
    public function getLetterInstance()
    {
        return $this->letterInstance;
    }
}