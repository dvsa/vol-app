<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractSystemParameter Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="system_parameter")
 */
abstract class AbstractSystemParameter implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Primary key
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id", length=32, nullable=false)
     */
    protected $id = '';

    /**
     * Param value
     *
     * @var string
     *
     * @ORM\Column(type="string", name="param_value", length=1024, nullable=true)
     */
    protected $paramValue;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;


    /**
     * Set the id
     *
     * @param string $id new value being set
     *
     * @return SystemParameter
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return string     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the param value
     *
     * @param string $paramValue new value being set
     *
     * @return SystemParameter
     */
    public function setParamValue($paramValue)
    {
        $this->paramValue = $paramValue;

        return $this;
    }

    /**
     * Get the param value
     *
     * @return string     */
    public function getParamValue()
    {
        return $this->paramValue;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return SystemParameter
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
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
