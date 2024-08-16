<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * Statement Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="statement",
 *    indexes={
 *        @ORM\Index(name="ix_statement_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_statement_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_statement_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_statement_contact_type", columns={"contact_type"}),
 *        @ORM\Index(name="ix_statement_statement_type", columns={"statement_type"}),
 *        @ORM\Index(name="fk_statement_contact_details1_idx", columns={"requestors_contact_details_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_statement_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Statement extends AbstractStatement implements OrganisationProviderInterface
{
    /**
     * Construct Statement entity
     * @param Cases   $case
     * @param RefData $statementType
     */
    public function __construct(Cases $case, RefData $statementType)
    {
        parent::__construct();
        $this->setCase($case);
        $this->setStatementType($statementType);
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation|\Dvsa\Olcs\Api\Entity\Organisation\Organisation[]|null
     */
    public function getRelatedOrganisation()
    {
        return $this->getCase()->getRelatedOrganisation();
    }
}
