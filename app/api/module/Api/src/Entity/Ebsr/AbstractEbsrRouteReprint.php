<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AbstractEbsrRouteReprint Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="ebsr_route_reprint",
 *    indexes={
 *        @ORM\Index(name="ix_ebsr_route_reprint_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_ebsr_route_reprint_olbs_key", columns={"olbs_key"}),
 *        @ORM\Index(name="ix_ebsr_route_reprint_requested_user_id", columns={"requested_user_id"})
 *    }
 * )
 */
abstract class AbstractEbsrRouteReprint implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;

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
     * Foreign Key to bus_reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id")
     */
    protected $busReg;

    /**
     * RequestedUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="requested_user_id", referencedColumnName="id")
     */
    protected $requestedUser;

    /**
     * Exception name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="exception_name", length=45, nullable=true)
     */
    protected $exceptionName;

    /**
     * Scale
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="scale", nullable=false, options={"default": 0})
     */
    protected $scale = 0;

    /**
     * Published timestamp
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="published_timestamp", nullable=true)
     */
    protected $publishedTimestamp;

    /**
     * Requested timestamp
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="requested_timestamp", nullable=false)
     */
    protected $requestedTimestamp;

    /**
     * Used to map FKs during ETL. Can be dropped safely when OLBS decommissioned
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

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
     * @return EbsrRouteReprint
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
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the requested user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $requestedUser new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setRequestedUser($requestedUser)
    {
        $this->requestedUser = $requestedUser;

        return $this;
    }

    /**
     * Get the requested user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getRequestedUser()
    {
        return $this->requestedUser;
    }

    /**
     * Set the exception name
     *
     * @param string $exceptionName new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setExceptionName($exceptionName)
    {
        $this->exceptionName = $exceptionName;

        return $this;
    }

    /**
     * Get the exception name
     *
     * @return string     */
    public function getExceptionName()
    {
        return $this->exceptionName;
    }

    /**
     * Set the scale
     *
     * @param bool $scale new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get the scale
     *
     * @return bool     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Set the published timestamp
     *
     * @param \DateTime $publishedTimestamp new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setPublishedTimestamp($publishedTimestamp)
    {
        $this->publishedTimestamp = $publishedTimestamp;

        return $this;
    }

    /**
     * Get the published timestamp
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPublishedTimestamp($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->publishedTimestamp);
        }

        return $this->publishedTimestamp;
    }

    /**
     * Set the requested timestamp
     *
     * @param \DateTime $requestedTimestamp new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setRequestedTimestamp($requestedTimestamp)
    {
        $this->requestedTimestamp = $requestedTimestamp;

        return $this;
    }

    /**
     * Get the requested timestamp
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRequestedTimestamp($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->requestedTimestamp);
        }

        return $this->requestedTimestamp;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return EbsrRouteReprint
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}