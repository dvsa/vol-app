<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;

/**
 * PsvDisc Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="psv_disc",
 *    indexes={
 *        @ORM\Index(name="ix_psv_disc_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_psv_disc_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_psv_disc_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_psv_disc_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class PsvDisc extends AbstractPsvDisc implements \Dvsa\Olcs\Api\Entity\OrganisationProviderInterface
{
    public const ERROR_CANT_EXCEED_TOT_AUTH = 'LIC-PSVDISC-1';
    public const ERROR_NO_DISCS_TO_PRINT = 'err_no_discs';

    public function __construct(Licence $licence)
    {
        $this->setLicence($licence);
    }

    public function cease()
    {
        $this->setCeasedDate(new \DateTime());
    }

    public function getRelatedOrganisation()
    {
        return $this->getLicence()->getOrganisation();
    }
}
