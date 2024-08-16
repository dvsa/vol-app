<?php

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
 * MessagingMessage Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="messaging_message",
 *    indexes={
 *        @ORM\Index(name="fk_messaging_message_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_message_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_message_messaging_conversation_id",
     *     columns={"messaging_conversation_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="message_content_id", columns={"messaging_content_id"})
 *    }
 * )
 */
abstract class AbstractMessagingMessage implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
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
     * Messaging content
     *
     * @var \Dvsa\Olcs\Api\Entity\Messaging\MessagingContent
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingContent",
     *     fetch="LAZY",
     *     cascade={"persist","remove"}
     * )
     * @ORM\JoinColumn(name="messaging_content_id", referencedColumnName="id", nullable=false)
     */
    protected $messagingContent;

    /**
     * Messaging conversation
     *
     * @var \Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation",
     *     fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="messaging_conversation_id", referencedColumnName="id", nullable=false)
     */
    protected $messagingConversation;

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
     * Document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="messagingMessage")
     */
    protected $documents;

    /**
     * User message read
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead",
     *     mappedBy="messagingMessage"
     * )
     */
    protected $userMessageReads;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->documents = new ArrayCollection();
        $this->userMessageReads = new ArrayCollection();
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return MessagingMessage
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
     * @return MessagingMessage
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
     * @return MessagingMessage
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
     * Set the messaging content
     *
     * @param \Dvsa\Olcs\Api\Entity\Messaging\MessagingContent $messagingContent entity being set as the value
     *
     * @return MessagingMessage
     */
    public function setMessagingContent($messagingContent)
    {
        $this->messagingContent = $messagingContent;

        return $this;
    }

    /**
     * Get the messaging content
     *
     * @return \Dvsa\Olcs\Api\Entity\Messaging\MessagingContent
     */
    public function getMessagingContent()
    {
        return $this->messagingContent;
    }

    /**
     * Set the messaging conversation
     *
     * @param \Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation $messagingConversation entity being set as the value
     *
     * @return MessagingMessage
     */
    public function setMessagingConversation($messagingConversation)
    {
        $this->messagingConversation = $messagingConversation;

        return $this;
    }

    /**
     * Get the messaging conversation
     *
     * @return \Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation
     */
    public function getMessagingConversation()
    {
        return $this->messagingConversation;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return MessagingMessage
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

    /**
     * Set the document
     *
     * @param ArrayCollection $documents collection being set as the value
     *
     * @return MessagingMessage
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param ArrayCollection|mixed $documents collection being added
     *
     * @return MessagingMessage
     */
    public function addDocuments($documents)
    {
        if ($documents instanceof ArrayCollection) {
            $this->documents = new ArrayCollection(
                array_merge(
                    $this->documents->toArray(),
                    $documents->toArray()
                )
            );
        } elseif (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
        }

        return $this;
    }

    /**
     * Remove a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being removed
     *
     * @return MessagingMessage
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the user message read
     *
     * @param ArrayCollection $userMessageReads collection being set as the value
     *
     * @return MessagingMessage
     */
    public function setUserMessageReads($userMessageReads)
    {
        $this->userMessageReads = $userMessageReads;

        return $this;
    }

    /**
     * Get the user message reads
     *
     * @return ArrayCollection
     */
    public function getUserMessageReads()
    {
        return $this->userMessageReads;
    }

    /**
     * Add a user message reads
     *
     * @param ArrayCollection|mixed $userMessageReads collection being added
     *
     * @return MessagingMessage
     */
    public function addUserMessageReads($userMessageReads)
    {
        if ($userMessageReads instanceof ArrayCollection) {
            $this->userMessageReads = new ArrayCollection(
                array_merge(
                    $this->userMessageReads->toArray(),
                    $userMessageReads->toArray()
                )
            );
        } elseif (!$this->userMessageReads->contains($userMessageReads)) {
            $this->userMessageReads->add($userMessageReads);
        }

        return $this;
    }

    /**
     * Remove a user message reads
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $userMessageReads collection being removed
     *
     * @return MessagingMessage
     */
    public function removeUserMessageReads($userMessageReads)
    {
        if ($this->userMessageReads->contains($userMessageReads)) {
            $this->userMessageReads->removeElement($userMessageReads);
        }

        return $this;
    }
}
