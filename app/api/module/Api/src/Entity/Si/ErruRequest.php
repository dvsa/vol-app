<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;

/**
 * ErruRequest Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="erru_request",
 *    indexes={
 *        @ORM\Index(name="ix_erru_request_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_erru_request_response_user_id", columns={"response_user_id"}),
 *        @ORM\Index(name="ix_erru_request_member_state_code", columns={"member_state_code"}),
 *        @ORM\Index(name="ix_erru_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_erru_request_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_erru_request_msi_type", columns={"msi_type"}),
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_erru_request_workflow_id", columns={"workflow_id"}),
 *        @ORM\UniqueConstraint(name="uk_erru_request_case_id", columns={"case_id"})
 *    }
 * )
 */
class ErruRequest extends AbstractErruRequest
{
    public const DEFAULT_CASE_TYPE = 'erru_case_t_msinre'; //MSI with no response entered
    public const FAILED_CASE_TYPE = 'erru_case_t_msirsf'; //MSI with response failure
    public const SENT_CASE_TYPE = 'erru_case_t_msirs'; //MSI with no response sent

    /**
     * ErruRequest constructor.
     *
     * @param string $originatingAuthority
     * @param string $transportUndertakingName
     * @param string $vrm
     * @param string $notificationNumber
     * @param string $workflowId
     */
    public function __construct(
        CaseEntity $case,
        RefData $msiType,
        CountryEntity $memberStateCode,
        DocumentEntity $requestDocument,
        RefData $communityLicenceStatus,
        string $communityLicenceNumber,
        $totAuthVehicles,
        $originatingAuthority,
        $transportUndertakingName,
        $vrm,
        $notificationNumber,
        $workflowId
    ) {
        parent::__construct();

        $this->case = $case;
        $this->msiType = $msiType;
        $this->memberStateCode = $memberStateCode;
        $this->requestDocument = $requestDocument;
        $this->communityLicenceStatus = $communityLicenceStatus;
        $this->communityLicenceNumber = $communityLicenceNumber;
        $this->totAuthVehicles = $totAuthVehicles;
        $this->originatingAuthority = $originatingAuthority;
        $this->transportUndertakingName = $transportUndertakingName;
        $this->vrm = $vrm;
        $this->notificationNumber = $notificationNumber;
        $this->workflowId = $workflowId;
    }

    /**
     * Returns whether the erru request is allowed to be modified (have si added and responses sent)
     */
    public function canModify(): bool
    {
        return $this->msiType->getId() === self::DEFAULT_CASE_TYPE;
    }

    /**
     * Updates the serious infringement response information, called while the response is being queued
     */
    public function queueErruResponse(
        UserEntity $user,
        \DateTime $responseDateTime,
        DocumentEntity $responseDocument
    ): void {
        $this->setResponseUser($user);
        $this->setResponseTime($responseDateTime);
        $this->setResponseDocument($responseDocument);
    }
}
