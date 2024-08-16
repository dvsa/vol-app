<?php

namespace Dvsa\Olcs\Api\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;

/**
 * Surrender Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="surrender",
 *    indexes={
 *        @ORM\Index(name="surrender_licence_document_ref_data_id_fk",
     *     columns={"licence_document_status"}),
 *        @ORM\Index(name="surrender_fk_community_licence_document_status_ref_data_id",
     *     columns={"community_licence_document_status"}),
 *        @ORM\Index(name="surrender_fk_digital_signature_id_ref_data_id",
     *     columns={"digital_signature_id"}),
 *        @ORM\Index(name="surrender_fk_last_modified", columns={"last_modified_by"}),
 *        @ORM\Index(name="surrender_status_index", columns={"status"}),
 *        @ORM\Index(name="surrender_created_by_index", columns={"created_by"}),
 *        @ORM\Index(name="surrender__index_licence", columns={"licence_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="surrender_id_uindex", columns={"id"})
 *    }
 * )
 */
class Surrender extends AbstractSurrender implements ContextProviderInterface
{
    public const SURRENDER_STATUS_START = 'surr_sts_start';
    public const SURRENDER_STATUS_CONTACTS_COMPLETE = 'surr_sts_contacts_complete';
    public const SURRENDER_STATUS_DISCS_COMPLETE = 'surr_sts_discs_complete';
    public const SURRENDER_STATUS_LIC_DOCS_COMPLETE = 'surr_sts_lic_docs_complete';
    public const SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE = 'surr_sts_comm_lic_docs_complete';
    public const SURRENDER_STATUS_DETAILS_CONFIRMED = 'surr_sts_details_confirmed';
    public const SURRENDER_STATUS_SUBMITTED = 'surr_sts_submitted';
    public const SURRENDER_STATUS_SIGNED = 'surr_sts_signed';
    public const SURRENDER_STATUS_APPROVED = 'surr_sts_approved';
    public const SURRENDER_DOC_STATUS_DESTROYED = 'doc_sts_destroyed';
    public const SURRENDER_DOC_STATUS_LOST = 'doc_sts_lost';
    public const SURRENDER_DOC_STATUS_STOLEN = 'doc_sts_stolen';
    public const SURRENDER_STATUS_WITHDRAWN = 'surr_sts_withdrawn';

    public function updateDigitalSignature(
        RefData $licenceStatus,
        RefData $surrenderStatus,
        RefData $signatureType,
        DigitalSignature $signature
    ): void {
        $this->signatureType = $signatureType;
        $this->digitalSignature = $signature;
        $this->status = $surrenderStatus;
        $this->licence->setStatus($licenceStatus);
    }

    public function getContextValue()
    {
        return $this->getId();
    }
}
