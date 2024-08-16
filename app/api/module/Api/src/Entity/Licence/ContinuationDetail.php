<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;

/**
 * ContinuationDetail Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="continuation_detail",
 *    indexes={
 *        @ORM\Index(name="fk_continuation_detail_continuation1_idx", columns={"continuation_id"}),
 *        @ORM\Index(name="fk_continuation_detail_licence1_idx", columns={"licence_id"}),
 *        @ORM\Index(name="fk_continuation_detail_ref_data1_idx", columns={"status"}),
 *        @ORM\Index(name="fk_continuation_detail_user1_idx", columns={"created_by"}),
 *        @ORM\Index(name="fk_continuation_detail_user2_idx", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_continuation_detail_checklist_document_id",
 *     columns={"checklist_document_id"}),
 *        @ORM\Index(name="ix_continuation_detail_received", columns={"received"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_continuation_detail_continuation_id_licence_id",
 *         columns={"licence_id","continuation_id"})
 *    }
 * )
 */
class ContinuationDetail extends AbstractContinuationDetail implements
    OrganisationProviderInterface,
    ContextProviderInterface
{
    public const STATUS_PREPARED     = 'con_det_sts_prepared';
    public const STATUS_PRINTING     = 'con_det_sts_printing';
    public const STATUS_PRINTED      = 'con_det_sts_printed';
    public const STATUS_UNACCEPTABLE = 'con_det_sts_unacceptable';
    public const STATUS_ACCEPTABLE   = 'con_det_sts_acceptable';
    public const STATUS_COMPLETE     = 'con_det_sts_complete';
    public const STATUS_ERROR        = 'con_det_sts_error';

    public const METHOD_EMAIL = 'email';
    public const METHOD_POST  = 'post';

    public const RESULT_LICENCE_CONTINUED = 'licence_continued';

    /**
     * Updates the digital signature for the continuation, runs as a side effect of govuk signin
     */
    public function updateDigitalSignature(RefData $signatureType, DigitalSignature $signature): void
    {
        $this->signatureType = $signatureType;
        $this->digitalSignature = $signature;
        $this->isDigital = true;
    }

    /**
     * Get Orgainsation owner
     *
     * @return Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->getLicence()->getOrganisation();
    }

    /**
     * Get context for document naming
     *
     * @return int
     */
    public function getContextValue()
    {
        return $this->getId();
    }

    /**
     * Get the total amount declared for this continuation
     *
     * @return float
     */
    public function getAmountDeclared()
    {
        return (float)$this->getAverageBalanceAmount()
            + (float)$this->getOverdraftAmount()
            + (float)$this->getFactoringAmount()
            + (float)$this->getOtherFinancesAmount();
    }
}
