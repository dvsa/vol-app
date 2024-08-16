<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;

/**
 * IrfoCountry Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irfo_country",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_country_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_country_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class IrfoCountry extends AbstractIrfoCountry
{
}
