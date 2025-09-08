<?php

namespace Dvsa\Olcs\Api\Entity\Ebsr;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Laminas\Json\Json as LaminasJson;
use Laminas\Json\Exception\RuntimeException as JsonException;

/**
 * EbsrSubmission Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="ebsr_submission",
 *    indexes={
 *        @ORM\Index(name="ix_ebsr_submission_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_ebsr_submission_ebsr_submission_status_id", columns={"ebsr_submission_status_id"}),
 *        @ORM\Index(name="ix_ebsr_submission_ebsr_submission_type_id", columns={"ebsr_submission_type_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_ebsr_submission_olbs_key", columns={"olbs_key"}),
 *        @ORM\UniqueConstraint(name="uk_ebsr_submission_document_id", columns={"document_id"})
 *    }
 * )
 */
class EbsrSubmission extends AbstractEbsrSubmission implements OrganisationProviderInterface
{
    public const UPLOADED_STATUS = 'ebsrs_uploaded';
    public const SUBMITTED_STATUS = 'ebsrs_submitted';
    public const VALIDATING_STATUS = 'ebsrs_validating';
    public const PROCESSED_STATUS = 'ebsrs_processed';
    public const PROCESSING_STATUS = 'ebsrs_processing';
    public const FAILED_STATUS = 'ebsrs_failed';

    public const DATA_REFRESH_SUBMISSION_TYPE = 'ebsrt_refresh';
    public const NEW_SUBMISSION_TYPE = 'ebsrt_new';
    public const UNKNOWN_SUBMISSION_TYPE = 'ebsrt_unknown';

    public const PROCESSING_DISPLAY_TYPE = 'ebsrd_processing';
    public const PROCESSED_DISPLAY_TYPE = 'ebsrd_processed';
    public const FAILED_DISPLAY_TYPE = 'ebsrd_failed';

    /**
     * A map of system ebsr statuses against the status that is displayed to the user
     *
     * @var array
     */
    public static $displayStatus = [
        self::PROCESSING_DISPLAY_TYPE => [
            self::SUBMITTED_STATUS,
            self::VALIDATING_STATUS,
            self::PROCESSING_STATUS
        ],
        self::PROCESSED_DISPLAY_TYPE => [
            self::PROCESSED_STATUS
        ],
        self::FAILED_DISPLAY_TYPE => [
            self::FAILED_STATUS
        ],
        'all_valid' => [
            self::SUBMITTED_STATUS,
            self::VALIDATING_STATUS,
            self::PROCESSING_STATUS,
            self::PROCESSED_STATUS,
            self::FAILED_STATUS
        ]
    ];

    /**
     * Creates EBSR submission
     *
     * @param Organisation $organisation         the organisation
     * @param RefData      $ebsrSubmissionStatus the submission status
     * @param RefData      $ebsrSubmissionType   the submission type
     * @param Document     $document             the document
     */
    public function __construct(
        Organisation $organisation,
        RefData $ebsrSubmissionStatus,
        RefData $ebsrSubmissionType,
        Document $document
    ) {
        $this->organisation = $organisation;
        $this->ebsrSubmissionStatus = $ebsrSubmissionStatus;
        $this->ebsrSubmissionType = $ebsrSubmissionType;
        $this->document = $document;
    }

    /**
     * Called when a previously uploaded EBSR pack is submitted
     *
     * @param RefData $ebsrSubmissionStatus submission status
     * @param RefData $ebsrSubmissionType   submission type
     *
     * @return void
     */
    public function submit(RefData $ebsrSubmissionStatus, RefData $ebsrSubmissionType)
    {
        $this->ebsrSubmissionStatus = $ebsrSubmissionStatus;
        $this->ebsrSubmissionType = $ebsrSubmissionType;
        $this->submittedDate = new \DateTime();
    }

    /**
     * Called when EBSR pack begins processing, includes a check that the status is correct
     *
     * @param RefData $ebsrSubmissionStatus submission status
     *
     * @return void
     * @throws ValidationException
     */
    public function beginValidating(RefData $ebsrSubmissionStatus)
    {
        if (!$this->isSubmitted()) {
            throw new ValidationException(['Only newly submitted records may be processed']);
        }

        $this->ebsrSubmissionStatus = $ebsrSubmissionStatus;
        $this->validationStart = new \DateTime();
    }

    /**
     * Called when validation of EBSR pack is completed
     */
    public function finishValidating(RefData $ebsrSubmissionStatus, array $ebsrSubmissionResult): void
    {
        $this->ebsrSubmissionStatus = $ebsrSubmissionStatus;
        $this->ebsrSubmissionResult = LaminasJson::encode($ebsrSubmissionResult);
        $this->validationEnd = new \DateTime();

        //if the submission hasn't failed (so far - this isn't yet a success) then also populate processStart timestamp
        if (!$this->isFailure()) {
            $this->processStart = $this->validationEnd;
        }
    }

    /**
     * Whether the EBSR submission is a failure
     *
     * @return bool
     */
    public function isFailure()
    {
        return $this->ebsrSubmissionStatus->getId() === self::FAILED_STATUS;
    }

    /**
     * Whether the EBSR submission is at the "submitted" stage
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->ebsrSubmissionStatus->getId() === self::SUBMITTED_STATUS;
    }

    /**
     * Whether the EBSR submission is being processed
     *
     * @return bool
     */
    public function isBeingProcessed()
    {
        $statuses = [
            self::SUBMITTED_STATUS,
            self::VALIDATING_STATUS,
            self::PROCESSING_STATUS
        ];

        return in_array($this->ebsrSubmissionStatus->getId(), $statuses);
    }

    /**
     * Whether the EBSR submission was successful
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->ebsrSubmissionStatus->getId() === self::PROCESSED_STATUS;
    }

    /**
     * Called when a submission has finished processing
     *
     * @param RefData $ebsrSubmissionStatus submission status
     *
     * @return void
     */
    public function finishProcessing(RefData $ebsrSubmissionStatus)
    {
        $this->ebsrSubmissionStatus = $ebsrSubmissionStatus;
        $this->processEnd = new \DateTime();
    }

    /**
     * Whether the EBSR submission is a data refresh
     *
     * @return bool
     */
    public function isDataRefresh()
    {
        return $this->ebsrSubmissionType->getId() === self::DATA_REFRESH_SUBMISSION_TYPE;
    }

    /**
     * Decode the submission result saved as json. We use a try/catch here so that legacy non-json data is dealt with
     *
     * @return array
     */
    public function getDecodedSubmissionResult()
    {
        try {
            $errorInfo = LaminasJson::decode($this->ebsrSubmissionResult, LaminasJson::TYPE_ARRAY);
        } catch (JsonException) {
            $errorInfo = [];
        }

        return $errorInfo;
    }

    /**
     * Gets the array of errors for the EBSR submission
     *
     * @return array
     */
    public function getErrors()
    {
        if (!$this->isFailure()) {
            return [];
        }

        $errorInfo = $this->getDecodedSubmissionResult();

        return $errorInfo['errors'] ?? [];
    }

    /**
     * Get the organisation the entity is linked to, used by CanAccessEbsrSubmission validator
     *
     * @return Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'isBeingProcessed' => $this->isBeingProcessed(),
            'isFailure' => $this->isFailure(),
            'isSuccess' => $this->isSuccess(),
            'errors' => $this->getErrors(),
        ];
    }
}
