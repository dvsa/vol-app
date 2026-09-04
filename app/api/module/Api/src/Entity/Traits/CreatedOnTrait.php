<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * CreatedOn Trait
 *
 * For the 402 tables whose created_on is nullable. The 13 that declare it NOT NULL use
 * CreatedOnNotNullTrait instead; the entity generator picks between them from the column.
 */
trait CreatedOnTrait
{
    use CreatedOnBehaviourTrait;

    /**
     * Created on
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'created_on', nullable: true)]
    protected $createdOn;
}
