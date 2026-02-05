<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Messaging;

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
 * AbstractMessagingConversation Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="messaging_conversation",
 *    indexes={
 *        @ORM\Index(name="fk_messaging_conversation_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_conversation_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_conversation_task_id", columns={"task_id"})
 *    }
 * )
 */
abstract class AbstractMessagingConversation implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Task
     *
     * @var \Dvsa\Olcs\Api\Entity\Task\Task
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", fetch="LAZY")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    protected $task;

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
     * Subject
     *
     * @var string
     *
     * @ORM\Column(type="string", name="subject", length=255, nullable=false)
     */
    protected $subject = '';

    /**
     * Is attachments enabled
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_attachments_enabled", nullable=true, options={"default": 0})
     */
    protected $isAttachmentsEnabled = 0;

    /**
     * Last read at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_read_at", nullable=true)
     */
    protected $lastReadAt;

    /**
     * Is closed
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_closed", nullable=true, options={"default": 0})
     */
    protected $isClosed = 0;

    /**
     * Is archived
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_archived", nullable=true, options={"default": 0})
     */
    protected $isArchived = 0;

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
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the task
     *
     * @param \Dvsa\Olcs\Api\Entity\Task\Task $task new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Task\Task     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
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
     * @return string     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the is attachments enabled
     *
     * @param bool $isAttachmentsEnabled new value being set
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
     * @return bool     */
    public function getIsAttachmentsEnabled()
    {
        return $this->isAttachmentsEnabled;
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
     * @return \DateTime     */
    public function getLastReadAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastReadAt);
        }

        return $this->lastReadAt;
    }

    /**
     * Set the is closed
     *
     * @param bool $isClosed new value being set
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
     * @return bool     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set the is archived
     *
     * @param bool $isArchived new value being set
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
     * @return bool     */
    public function getIsArchived()
    {
        return $this->isArchived;
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
