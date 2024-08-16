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
 * MessagingUserMessageRead Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="messaging_user_message_read",
 *    indexes={
 *        @ORM\Index(name="IDX_B9D49F7EA76ED395", columns={"user_id"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_messaging_message_id",
     *     columns={"messaging_message_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="ck_unique_user_id_message_id", columns={"user_id","messaging_message_id"})
 *    }
 * )
 */
abstract class AbstractMessagingUserMessageRead implements BundleSerializableInterface, JsonSerializable
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
     * Last read on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_read_on", nullable=false)
     */
    protected $lastReadOn;

    /**
     * Messaging message
     *
     * @var \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage",
     *     fetch="LAZY",
     *     inversedBy="userMessageReads"
     * )
     * @ORM\JoinColumn(name="messaging_message_id", referencedColumnName="id", nullable=false)
     */
    protected $messagingMessage;

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
     * @return MessagingUserMessageRead
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
     * @return MessagingUserMessageRead
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
     * @return MessagingUserMessageRead
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
     * Set the last read on
     *
     * @param \DateTime $lastReadOn new value being set
     *
     * @return MessagingUserMessageRead
     */
    public function setLastReadOn($lastReadOn)
    {
        $this->lastReadOn = $lastReadOn;

        return $this;
    }

    /**
     * Get the last read on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getLastReadOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastReadOn);
        }

        return $this->lastReadOn;
    }

    /**
     * Set the messaging message
     *
     * @param \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage $messagingMessage entity being set as the value
     *
     * @return MessagingUserMessageRead
     */
    public function setMessagingMessage($messagingMessage)
    {
        $this->messagingMessage = $messagingMessage;

        return $this;
    }

    /**
     * Get the messaging message
     *
     * @return \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage
     */
    public function getMessagingMessage()
    {
        return $this->messagingMessage;
    }

    /**
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user entity being set as the value
     *
     * @return MessagingUserMessageRead
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
     * @return MessagingUserMessageRead
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
