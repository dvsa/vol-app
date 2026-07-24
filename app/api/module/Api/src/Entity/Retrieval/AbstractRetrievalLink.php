<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Retrieval;

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
 * AbstractRetrievalLink Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="retrieval_link",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_link_expires_at", columns={"expires_at"}),
 *        @ORM\Index(name="ix_retrieval_link_flow_key", columns={"flow_key"}),
 *        @ORM\Index(name="ix_retrieval_link_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_retrieval_link_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_retrieval_link_token", columns={"token"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_retrieval_link_token", columns={"token"})
 *    }
 * )
 */
abstract class AbstractRetrievalLink implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Token
     *
     * @var string
     *
     * @ORM\Column(type="string", name="token", length=64, nullable=false)
     */
    protected $token = '';

    /**
     * Gate mode
     *
     * @var string
     *
     * @ORM\Column(type="string", name="gate_mode", length=16, nullable=false)
     */
    protected $gateMode = '';

    /**
     * Flow key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="flow_key", length=64, nullable=false)
     */
    protected $flowKey = '';

    /**
     * Source context
     *
     * @var string
     *
     * @ORM\Column(type="string", name="source_context", length=255, nullable=true)
     */
    protected $sourceContext;

    /**
     * Recipient email
     *
     * @var string
     *
     * @ORM\Column(type="string", name="recipient_email", length=255, nullable=true)
     */
    protected $recipientEmail;

    /**
     * Expires at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="expires_at", nullable=false)
     */
    protected $expiresAt;

    /**
     * Revoked at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="revoked_at", nullable=true)
     */
    protected $revokedAt;

    /**
     * Last accessed on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_accessed_on", nullable=true)
     */
    protected $lastAccessedOn;

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
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Documents
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkDocument", mappedBy="retrievalLink")
     */
    protected $documents;

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
        $this->documents = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return RetrievalLink
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
     * Set the token
     *
     * @param string $token new value being set
     *
     * @return RetrievalLink
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the gate mode
     *
     * @param string $gateMode new value being set
     *
     * @return RetrievalLink
     */
    public function setGateMode($gateMode)
    {
        $this->gateMode = $gateMode;

        return $this;
    }

    /**
     * Get the gate mode
     *
     * @return string
     */
    public function getGateMode()
    {
        return $this->gateMode;
    }

    /**
     * Set the flow key
     *
     * @param string $flowKey new value being set
     *
     * @return RetrievalLink
     */
    public function setFlowKey($flowKey)
    {
        $this->flowKey = $flowKey;

        return $this;
    }

    /**
     * Get the flow key
     *
     * @return string
     */
    public function getFlowKey()
    {
        return $this->flowKey;
    }

    /**
     * Set the source context
     *
     * @param string $sourceContext new value being set
     *
     * @return RetrievalLink
     */
    public function setSourceContext($sourceContext)
    {
        $this->sourceContext = $sourceContext;

        return $this;
    }

    /**
     * Get the source context
     *
     * @return string
     */
    public function getSourceContext()
    {
        return $this->sourceContext;
    }

    /**
     * Set the recipient email
     *
     * @param string $recipientEmail new value being set
     *
     * @return RetrievalLink
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    /**
     * Get the recipient email
     *
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * Set the expires at
     *
     * @param \DateTime $expiresAt new value being set
     *
     * @return RetrievalLink
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Get the expires at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getExpiresAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->expiresAt);
        }

        return $this->expiresAt;
    }

    /**
     * Set the revoked at
     *
     * @param \DateTime $revokedAt new value being set
     *
     * @return RetrievalLink
     */
    public function setRevokedAt($revokedAt)
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * Get the revoked at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getRevokedAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->revokedAt);
        }

        return $this->revokedAt;
    }

    /**
     * Set the last accessed on
     *
     * @param \DateTime $lastAccessedOn new value being set
     *
     * @return RetrievalLink
     */
    public function setLastAccessedOn($lastAccessedOn)
    {
        $this->lastAccessedOn = $lastAccessedOn;

        return $this;
    }

    /**
     * Get the last accessed on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getLastAccessedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastAccessedOn);
        }

        return $this->lastAccessedOn;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return RetrievalLink
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return RetrievalLink
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return RetrievalLink
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
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being set as the value
     *
     * @return RetrievalLink
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $documents collection being added
     *
     * @return RetrievalLink
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
     * @return RetrievalLink
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
