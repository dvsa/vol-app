<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\PrintScan;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AbstractPrinter Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="printer")
 */
abstract class AbstractPrinter implements BundleSerializableInterface, JsonSerializable
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
     * Printer tray
     *
     * @var string
     *
     * @ORM\Column(type="string", name="printer_tray", length=45, nullable=true)
     */
    protected $printerTray;

    /**
     * Printer name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="printer_name", length=45, nullable=true)
     */
    protected $printerName;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * TeamPrinters
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter", mappedBy="printer")
     */
    protected $teamPrinters;

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
        $this->teamPrinters = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Printer
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
     * Set the printer tray
     *
     * @param string $printerTray new value being set
     *
     * @return Printer
     */
    public function setPrinterTray($printerTray)
    {
        $this->printerTray = $printerTray;

        return $this;
    }

    /**
     * Get the printer tray
     *
     * @return string     */
    public function getPrinterTray()
    {
        return $this->printerTray;
    }

    /**
     * Set the printer name
     *
     * @param string $printerName new value being set
     *
     * @return Printer
     */
    public function setPrinterName($printerName)
    {
        $this->printerName = $printerName;

        return $this;
    }

    /**
     * Get the printer name
     *
     * @return string     */
    public function getPrinterName()
    {
        return $this->printerName;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Printer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the team printers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $teamPrinters collection being set as the value
     *
     * @return Printer
     */
    public function setTeamPrinters($teamPrinters)
    {
        $this->teamPrinters = $teamPrinters;

        return $this;
    }

    /**
     * Get the team printers
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTeamPrinters()
    {
        return $this->teamPrinters;
    }

    /**
     * Add a team printers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $teamPrinters collection being added
     *
     * @return Printer
     */
    public function addTeamPrinters($teamPrinters)
    {
        if ($teamPrinters instanceof ArrayCollection) {
            $this->teamPrinters = new ArrayCollection(
                array_merge(
                    $this->teamPrinters->toArray(),
                    $teamPrinters->toArray()
                )
            );
        } elseif (!$this->teamPrinters->contains($teamPrinters)) {
            $this->teamPrinters->add($teamPrinters);
        }

        return $this;
    }

    /**
     * Remove a team printers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $teamPrinters collection being removed
     *
     * @return Printer
     */
    public function removeTeamPrinters($teamPrinters)
    {
        if ($this->teamPrinters->contains($teamPrinters)) {
            $this->teamPrinters->removeElement($teamPrinters);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}