<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * IrfoPartner Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irfo_partner",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_partner_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_irfo_partner_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_partner_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_irfo_partner_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class IrfoPartner extends AbstractIrfoPartner
{
    public function __construct(Organisation $organisation, $name)
    {
        $this->organisation = $organisation;
        $this->name = $name;
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedValues()
    {
        return [
            'organisation' => null,
        ];
    }
}
