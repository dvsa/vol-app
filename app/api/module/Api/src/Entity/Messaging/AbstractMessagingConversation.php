<?php

namespace Dvsa\Olcs\Api\Entity\Messaging;

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
 * MessagingConversation Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="messaging_conversation",
 *    indexes={
 *        @ORM\Index(name="fk_messaging_conversation_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_conversation_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_conversation_task_id", columns={"task_id"})
 *    }
 * )
 */
abstract class AbstractMessagingConversation implements BundleSerializableInterface, JsonSerializable
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
     * Is archived
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_archived", nullable=true, options={"default": 0})
     */
    protected $isArchived = 0;

    /**
     * Is attachments enabled
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="is_attachments_enabled",
     *     nullable=true,
     *     options={"default": 0})
     */
    protected $isAttachmentsEnabled = 0;

    /**
     * Is closed
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_closed", nullable=true, options={"default": 0})
     */
    protected $isClosed = 0;

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
     * Last read at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_read_at", nullable=true)
     */
    protected $lastReadAt;

    /**
     * Subject
     *
     * @var string
     *
     * @ORM\Column(type="string", name="subject", length=255, nullable=false)
     */
    protected $subject;

    /**
     * Task
     *
     * @var \Dvsa\Olcs\Api\Entity\Task\Task
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", fetch="LAZY")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    protected $task;

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
     * @return MessagingConversation
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
     * @return MessagingConversation
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
     * Set the is archived
     *
     * @param boolean $isArchived new value being set
     *
     * @return MessagingConversation
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * Get the is archived
     *
     * @return boolean
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * Set the is attachments enabled
     *
     * @param boolean $isAttachmentsEnabled new value being set
     *
     * @return MessagingConversation
     */
    public function setIsAttachmentsEnabled($isAttachmentsEnabled)
    {
        $this->isAttachmentsEnabled = $isAttachmentsEnabled;

        return $this;
    }

    /**
     * Get the is attachments enabled
     *
     * @return boolean
     */
    public function getIsAttachmentsEnabled()
    {
        return $this->isAttachmentsEnabled;
    }

    /**
     * Set the is closed
     *
     * @param boolean $isClosed new value being set
     *
     * @return MessagingConversation
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get the is closed
     *
     * @return boolean
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return MessagingConversation
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
     * Set the last read at
     *
     * @param \DateTime $lastReadAt new value being set
     *
     * @return MessagingConversation
     */
    public function setLastReadAt($lastReadAt)
    {
        $this->lastReadAt = $lastReadAt;

        return $this;
    }

    /**
     * Get the last read at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getLastReadAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastReadAt);
        }

        return $this->lastReadAt;
    }

    /**
     * Set the subject
     *
     * @param string $subject new value being set
     *
     * @return MessagingConversation
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the task
     *
     * @param \Dvsa\Olcs\Api\Entity\Task\Task $task entity being set as the value
     *
     * @return MessagingConversation
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get the task
     *
     * @return \Dvsa\Olcs\Api\Entity\Task\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return MessagingConversation
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
