<?php

namespace Dvsa\Olcs\Api\Entity\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * EbsrRouteReprint Abstract Entity
 *
 * Auto-Generated
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
    use ClearPropertiesTrait;

    /**
     * Bus reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id", nullable=false)
     */
    protected $busReg;

    /**
     * Exception name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="exception_name", length=45, nullable=true)
     */
    protected $exceptionName;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

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
     * Requested user
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="requested_user_id", referencedColumnName="id", nullable=false)
     */
    protected $requestedUser;

    /**
     * Scale
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="scale", nullable=false, options={"default": 0})
     */
    protected $scale = 0;

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg
     */
    public function getBusReg()
    {
        return $this->busReg;
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
     * @return string
     */
    public function getExceptionName()
    {
        return $this->exceptionName;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
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
     * @return \DateTime|string

     */
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
     * @return \DateTime|string

     */
    public function getRequestedTimestamp($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->requestedTimestamp);
        }

        return $this->requestedTimestamp;
    }

    /**
     * Set the requested user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $requestedUser entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getRequestedUser()
    {
        return $this->requestedUser;
    }

    /**
     * Set the scale
     *
     * @param boolean $scale new value being set
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
     * @return boolean
     */
    public function getScale()
    {
        return $this->scale;
    }
}
