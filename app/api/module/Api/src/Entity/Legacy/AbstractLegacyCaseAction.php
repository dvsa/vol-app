<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Legacy;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractLegacyCaseAction Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="legacy_case_action")
 */
abstract class AbstractLegacyCaseAction implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    protected $id = 0;

    /**
     * Examples, No Action, Warning, Interview
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=45, nullable=true)
     */
    protected $description;

    /**
     * isDriver
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_driver", nullable=false, options={"default": 0})
     */
    protected $isDriver = 0;


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return LegacyCaseAction
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return LegacyCaseAction
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
     * Set the is driver
     *
     * @param string $isDriver new value being set
     *
     * @return LegacyCaseAction
     */
    public function setIsDriver($isDriver)
    {
        $this->isDriver = $isDriver;

        return $this;
    }

    /**
     * Get the is driver
     *
     * @return string     */
    public function getIsDriver()
    {
        return $this->isDriver;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}