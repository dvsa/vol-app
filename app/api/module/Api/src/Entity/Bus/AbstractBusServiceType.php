<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * BusServiceType Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="bus_service_type")
 */
abstract class AbstractBusServiceType implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;

    /**
     * Bus reg
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg",
     *     mappedBy="busServiceTypes",
     *     fetch="LAZY"
     * )
     */
    protected $busRegs;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=70, nullable=true)
     */
    protected $description;

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
     * Txc name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="txc_name", length=70, nullable=true)
     */
    protected $txcName;

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
        $this->busRegs = new ArrayCollection();
    }

    /**
     * Set the bus reg
     *
     * @param ArrayCollection $busRegs collection being set as the value
     *
     * @return BusServiceType
     */
    public function setBusRegs($busRegs)
    {
        $this->busRegs = $busRegs;

        return $this;
    }

    /**
     * Get the bus regs
     *
     * @return ArrayCollection
     */
    public function getBusRegs()
    {
        return $this->busRegs;
    }

    /**
     * Add a bus regs
     *
     * @param ArrayCollection|mixed $busRegs collection being added
     *
     * @return BusServiceType
     */
    public function addBusRegs($busRegs)
    {
        if ($busRegs instanceof ArrayCollection) {
            $this->busRegs = new ArrayCollection(
                array_merge(
                    $this->busRegs->toArray(),
                    $busRegs->toArray()
                )
            );
        } elseif (!$this->busRegs->contains($busRegs)) {
            $this->busRegs->add($busRegs);
        }

        return $this;
    }

    /**
     * Remove a bus regs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busRegs collection being removed
     *
     * @return BusServiceType
     */
    public function removeBusRegs($busRegs)
    {
        if ($this->busRegs->contains($busRegs)) {
            $this->busRegs->removeElement($busRegs);
        }

        return $this;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return BusServiceType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return BusServiceType
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
     * Set the txc name
     *
     * @param string $txcName new value being set
     *
     * @return BusServiceType
     */
    public function setTxcName($txcName)
    {
        $this->txcName = $txcName;

        return $this;
    }

    /**
     * Get the txc name
     *
     * @return string
     */
    public function getTxcName()
    {
        return $this->txcName;
    }
}
