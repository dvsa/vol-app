<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * CompanySubsidiary Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="company_subsidiary",
 *    indexes={
 *        @ORM\Index(name="ix_company_subsidiary_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_company_subsidiary_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_company_subsidiary_licence1_idx", columns={"licence_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_company_subsidiary_olbs_key", columns={"olbs_key","licence_id"})
 *    }
 * )
 */
class CompanySubsidiary extends AbstractCompanySubsidiary implements OrganisationProviderInterface
{
    public function __construct($name, $companyNo, Licence $licence)
    {
        $this->setName($name);
        $this->setCompanyNo($companyNo);
        $this->setLicence($licence);
    }

    /**
     * @inheritdoc
     */
    public function getRelatedOrganisation()
    {
        return $this->getLicence()->getOrganisation();
    }

    protected function getCalculatedValues()
    {
        return ['licence' => null];
    }
}
