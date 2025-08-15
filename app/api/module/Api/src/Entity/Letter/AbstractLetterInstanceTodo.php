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
 * LetterInstanceTodo Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_instance_todo",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_todo_letter_todo_version_id", columns={"letter_todo_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_todo_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_todo_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLetterInstanceTodo implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Completed
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="completed", nullable=false, options={"default": 0})
     */
    protected $completed = false;

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
     * Letter instance
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstance
     *
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstance",
     *     fetch="LAZY",
     *     inversedBy="letterInstanceTodos"
     * )
     * @ORM\JoinColumn(name="letter_instance_id", referencedColumnName="id", nullable=false)
     */
    protected $letterInstance;

    /**
     * Letter todo version
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_todo_version_id", referencedColumnName="id", nullable=false)
     */
    protected $letterTodoVersion;

    /**
     * Set the completed
     *
     * @param bool $completed new value being set
     *
     * @return self
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get the completed
     *
     * @return bool
     */
    public function getCompleted()
    {
        return $this->completed;
    }

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

    /**
     * Set the letter todo version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion $letterTodoVersion entity being set as the value
     *
     * @return self
     */
    public function setLetterTodoVersion($letterTodoVersion)
    {
        $this->letterTodoVersion = $letterTodoVersion;

        return $this;
    }

    /**
     * Get the letter todo version
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion
     */
    public function getLetterTodoVersion()
    {
        return $this->letterTodoVersion;
    }
}