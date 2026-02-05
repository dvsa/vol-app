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
 * AbstractMessagingUserMessageRead Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="messaging_user_message_read",
 *    indexes={
 *        @ORM\Index(name="ck_unique_user_id_message_id", columns={"user_id", "messaging_message_id"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_messaging_message_id", columns={"messaging_message_id"}),
 *        @ORM\Index(name="IDX_B9D49F7EA76ED395", columns={"user_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="ck_unique_user_id_message_id", columns={"user_id", "messaging_message_id"})
 *    }
 * )
 */
abstract class AbstractMessagingUserMessageRead implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * User
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * MessagingMessage
     *
     * @var \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage", fetch="LAZY")
     * @ORM\JoinColumn(name="messaging_message_id", referencedColumnName="id")
     */
    protected $messagingMessage;

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
     * Last read on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_read_on", nullable=false)
     */
    protected $lastReadOn;

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
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the messaging message
     *
     * @param \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage $messagingMessage new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage     */
    public function getMessagingMessage()
    {
        return $this->messagingMessage;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
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
     * @return \DateTime     */
    public function getLastReadOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastReadOn);
        }

        return $this->lastReadOn;
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
