<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Everything about createdOn except the column mapping, which differs by nullability and so
 * is declared by the trait that uses this one - CreatedOnTrait for the nullable columns,
 * CreatedOnNotNullTrait for the handful the schema declares NOT NULL.
 *
 * getCreatedOn() calls asDateTime(), so an entity using either must also use ProcessDateTrait.
 */
trait CreatedOnBehaviourTrait
{
    /**
     * Set the createdOn field on persist
     *
     * @return void
     */
    #[ORM\PrePersist]
    public function setCreatedOnBeforePersist()
    {
        $this->createdOn = new \DateTime();
    }

    /**
     * Set the created on
     *
     * @param \DateTime $createdOn new value being set
     *
     * @return $this
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get the created on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string
     */
    public function getCreatedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->createdOn);
        }

        return $this->createdOn;
    }
}
