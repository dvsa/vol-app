<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Retrieval;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AbstractRetrievalOtp Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="retrieval_otp",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_otp_retrieval_link_id", columns={"retrieval_link_id"}),
 *        @ORM\Index(name="ix_retrieval_otp_expires_at", columns={"expires_at"})
 *    }
 * )
 */
abstract class AbstractRetrievalOtp implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;

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
     * Foreign Key to retrieval_link
     *
     * @var \Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink", fetch="LAZY")
     * @ORM\JoinColumn(name="retrieval_link_id", referencedColumnName="id")
     */
    protected $retrievalLink;

    /**
     * Code hash
     *
     * @var string
     *
     * @ORM\Column(type="string", name="code_hash", length=255, nullable=false)
     */
    protected $codeHash = '';

    /**
     * Attempts
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="attempts", nullable=false, options={"default": 0})
     */
    protected $attempts = 0;

    /**
     * Max attempts
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="max_attempts", nullable=false, options={"default": 5})
     */
    protected $maxAttempts = 5;

    /**
     * Expires at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="expires_at", nullable=false)
     */
    protected $expiresAt;

    /**
     * Consumed at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="consumed_at", nullable=true)
     */
    protected $consumedAt;

    /**
     * Invalidated at
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="invalidated_at", nullable=true)
     */
    protected $invalidatedAt;

    /**
     * Request ip
     *
     * @var string
     *
     * @ORM\Column(type="string", name="request_ip", length=45, nullable=true)
     */
    protected $requestIp;

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
     * @return RetrievalOtp
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
     * Set the retrieval link
     *
     * @param \Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink $retrievalLink new value being set
     *
     * @return RetrievalOtp
     */
    public function setRetrievalLink($retrievalLink)
    {
        $this->retrievalLink = $retrievalLink;

        return $this;
    }

    /**
     * Get the retrieval link
     *
     * @return \Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink
     */
    public function getRetrievalLink()
    {
        return $this->retrievalLink;
    }

    /**
     * Set the code hash
     *
     * @param string $codeHash new value being set
     *
     * @return RetrievalOtp
     */
    public function setCodeHash($codeHash)
    {
        $this->codeHash = $codeHash;

        return $this;
    }

    /**
     * Get the code hash
     *
     * @return string
     */
    public function getCodeHash()
    {
        return $this->codeHash;
    }

    /**
     * Set the attempts
     *
     * @param int $attempts new value being set
     *
     * @return RetrievalOtp
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Get the attempts
     *
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Set the max attempts
     *
     * @param int $maxAttempts new value being set
     *
     * @return RetrievalOtp
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    /**
     * Get the max attempts
     *
     * @return int
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * Set the expires at
     *
     * @param \DateTime $expiresAt new value being set
     *
     * @return RetrievalOtp
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
     * Set the consumed at
     *
     * @param \DateTime $consumedAt new value being set
     *
     * @return RetrievalOtp
     */
    public function setConsumedAt($consumedAt)
    {
        $this->consumedAt = $consumedAt;

        return $this;
    }

    /**
     * Get the consumed at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getConsumedAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->consumedAt);
        }

        return $this->consumedAt;
    }

    /**
     * Set the invalidated at
     *
     * @param \DateTime $invalidatedAt new value being set
     *
     * @return RetrievalOtp
     */
    public function setInvalidatedAt($invalidatedAt)
    {
        $this->invalidatedAt = $invalidatedAt;

        return $this;
    }

    /**
     * Get the invalidated at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getInvalidatedAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->invalidatedAt);
        }

        return $this->invalidatedAt;
    }

    /**
     * Set the request ip
     *
     * @param string $requestIp new value being set
     *
     * @return RetrievalOtp
     */
    public function setRequestIp($requestIp)
    {
        $this->requestIp = $requestIp;

        return $this;
    }

    /**
     * Get the request ip
     *
     * @return string
     */
    public function getRequestIp()
    {
        return $this->requestIp;
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
