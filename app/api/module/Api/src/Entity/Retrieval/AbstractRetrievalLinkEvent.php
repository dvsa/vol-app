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
 * AbstractRetrievalLinkEvent Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="retrieval_link_event",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_link_event_retrieval_link_id", columns={"retrieval_link_id"}),
 *        @ORM\Index(name="ix_retrieval_link_event_type", columns={"event_type"}),
 *        @ORM\Index(name="ix_retrieval_link_event_created_on", columns={"created_on"})
 *    }
 * )
 */
abstract class AbstractRetrievalLinkEvent implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * @ORM\Column(type="bigint", name="id", nullable=false)
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
     * Event type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="event_type", length=32, nullable=false)
     */
    protected $eventType = '';

    /**
     * Member ref
     *
     * @var string
     *
     * @ORM\Column(type="string", name="member_ref", length=64, nullable=true)
     */
    protected $memberRef;

    /**
     * Ip
     *
     * @var string
     *
     * @ORM\Column(type="string", name="ip", length=45, nullable=true)
     */
    protected $ip;

    /**
     * User agent
     *
     * @var string
     *
     * @ORM\Column(type="string", name="user_agent", length=255, nullable=true)
     */
    protected $userAgent;

    /**
     * Detail
     *
     * @var string
     *
     * @ORM\Column(type="string", name="detail", length=255, nullable=true)
     */
    protected $detail;

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
     * @return RetrievalLinkEvent
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
     * @return RetrievalLinkEvent
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
     * Set the event type
     *
     * @param string $eventType new value being set
     *
     * @return RetrievalLinkEvent
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * Get the event type
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Set the member ref
     *
     * @param string $memberRef new value being set
     *
     * @return RetrievalLinkEvent
     */
    public function setMemberRef($memberRef)
    {
        $this->memberRef = $memberRef;

        return $this;
    }

    /**
     * Get the member ref
     *
     * @return string
     */
    public function getMemberRef()
    {
        return $this->memberRef;
    }

    /**
     * Set the ip
     *
     * @param string $ip new value being set
     *
     * @return RetrievalLinkEvent
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get the ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set the user agent
     *
     * @param string $userAgent new value being set
     *
     * @return RetrievalLinkEvent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get the user agent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set the detail
     *
     * @param string $detail new value being set
     *
     * @return RetrievalLinkEvent
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get the detail
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
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
