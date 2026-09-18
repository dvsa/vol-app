<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * CreatedOn Trait for the tables that declare created_on NOT NULL - the read-audit tables,
 * the letter version tables and a few others, where a row without a timestamp is meaningless
 * and created_on carries a unique key.
 *
 * Identical to CreatedOnTrait apart from the mapping's nullable flag. setCreatedOnBeforePersist()
 * always populates the column on insert, so the constraint cannot be reached through the ORM.
 */
trait CreatedOnNotNullTrait
{
    use CreatedOnBehaviourTrait;

    /**
     * Created on
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'created_on', nullable: false)]
    protected $createdOn;
}
