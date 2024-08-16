<?php

namespace Dvsa\Olcs\Api\Entity\Task;

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
 * TaskAlphaSplit Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="task_alpha_split",
 *    indexes={
 *        @ORM\Index(name="ix_task_alpha_split_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_task_alpha_split_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_task_alpha_split_task_allocation_rule_id",
     *     columns={"task_allocation_rule_id"}),
 *        @ORM\Index(name="ix_task_alpha_split_user_id", columns={"user_id"})
 *    }
 * )
 */
abstract class AbstractTaskAlphaSplit implements BundleSerializableInterface, JsonSerializable
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
     * Letters
     *
     * @var string
     *
     * @ORM\Column(type="string", name="letters", length=50, nullable=false)
     */
    protected $letters;

    /**
     * Task allocation rule
     *
     * @var \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule",
     *     fetch="LAZY",
     *     inversedBy="taskAlphaSplits"
     * )
     * @ORM\JoinColumn(name="task_allocation_rule_id", referencedColumnName="id", nullable=false)
     */
    protected $taskAllocationRule;

    /**
     * User
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

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
     * @return TaskAlphaSplit
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
     * @return TaskAlphaSplit
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
     * @return TaskAlphaSplit
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
     * Set the letters
     *
     * @param string $letters new value being set
     *
     * @return TaskAlphaSplit
     */
    public function setLetters($letters)
    {
        $this->letters = $letters;

        return $this;
    }

    /**
     * Get the letters
     *
     * @return string
     */
    public function getLetters()
    {
        return $this->letters;
    }

    /**
     * Set the task allocation rule
     *
     * @param \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule $taskAllocationRule entity being set as the value
     *
     * @return TaskAlphaSplit
     */
    public function setTaskAllocationRule($taskAllocationRule)
    {
        $this->taskAllocationRule = $taskAllocationRule;

        return $this;
    }

    /**
     * Get the task allocation rule
     *
     * @return \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule
     */
    public function getTaskAllocationRule()
    {
        return $this->taskAllocationRule;
    }

    /**
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user entity being set as the value
     *
     * @return TaskAlphaSplit
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TaskAlphaSplit
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
