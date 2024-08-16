<?php

namespace Dvsa\Olcs\Api\Entity\Queue;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Queue Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="queue",
 *    indexes={
 *        @ORM\Index(name="ix_queue_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_queue_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_queue_type", columns={"type"}),
 *        @ORM\Index(name="ix_queue_status", columns={"status"})
 *    }
 * )
 */
class Queue extends AbstractQueue
{
    // Message statuses
    public const STATUS_QUEUED = 'que_sts_queued';
    public const STATUS_PROCESSING = 'que_sts_processing';
    public const STATUS_COMPLETE = 'que_sts_complete';
    public const STATUS_FAILED = 'que_sts_failed';

    protected $statuses = [
        self::STATUS_QUEUED,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETE,
        self::STATUS_FAILED
    ];

    // Message types
    public const TYPE_COMPANIES_HOUSE_COMPARE = 'que_typ_ch_compare';
    public const TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER = 'que_typ_cont_check_rem_gen_let';
    public const TYPE_CPID_EXPORT_CSV = 'que_typ_cpid_export_csv';
    public const TYPE_CONT_CHECKLIST = 'que_typ_cont_checklist';
    public const TYPE_TM_SNAPSHOT = 'que_typ_tm_snapshot';
    public const TYPE_CPMS_REPORT_DOWNLOAD = 'que_typ_cpms_report_download';
    public const TYPE_EBSR_REQUEST_MAP = 'que_typ_ebsr_request_map';
    public const TYPE_EBSR_PACK = 'que_typ_ebsr_pack';
    public const TYPE_EBSR_PACK_FAILED = 'que_typ_ebsr_pack_failed';
    public const TYPE_SEND_MSI_RESPONSE = 'que_typ_msi_response';
    public const TYPE_EMAIL = 'que_typ_email';
    public const TYPE_PRINT = 'que_typ_print';
    public const TYPE_DISC_PRINTING_PRINT = 'que_typ_disc_printing_print';
    public const TYPE_DISC_PRINTING = 'que_typ_disc_printing';
    public const TYPE_PERMIT_GENERATE = 'que_typ_permit_generate';
    public const TYPE_PERMIT_PRINT = 'que_typ_permit_print';
    public const TYPE_PERMIT_REPORT = 'que_typ_permit_report';
    public const TYPE_CREATE_GOODS_VEHICLE_LIST = 'que_typ_create_gds_vehicle_list';
    public const TYPE_CREATE_PSV_VEHICLE_LIST = 'que_typ_create_psv_vehicle_list';
    public const TYPE_UPDATE_NYSIIS_TM_NAME = 'que_typ_update_nysiis_tm_name';
    public const TYPE_CNS = 'que_typ_cns';
    public const TYPE_CNS_EMAIL = 'que_typ_cns_email';
    public const TYPE_CREATE_COM_LIC = 'que_typ_create_com_lic';
    public const TYPE_REMOVE_DELETED_DOCUMENTS = 'que_typ_remove_deleted_docs';
    public const TYPE_CREATE_CONTINUATION_SNAPSHOT = 'que_typ_cont_shapshot';
    public const TYPE_CONT_DIGITAL_REMINDER = 'que_typ_cont_digital_reminder';
    public const TYPE_PERMITS_POST_SUBMIT = 'que_typ_permits_post_submit';
    public const TYPE_IRHP_APPLICATION_PERMITS_ALLOCATE = 'que_typ_irhp_permits_allocate';
    public const TYPE_RUN_ECMT_SCORING = 'que_typ_run_ecmt_scoring';
    public const TYPE_ACCEPT_ECMT_SCORING = 'que_typ_accept_ecmt_scoring';
    public const TYPE_COMM_LIC_BULK_REPRINT = 'que_typ_comm_lic_bulk_reprint';
    public const TYPE_LETTER_BULK_UPLOAD = 'que_typ_letter_bulk_upload';
    public const TYPE_EMAIL_BULK_UPLOAD = 'que_typ_email_bulk_upload';
    public const TYPE_POST_SCORING_EMAIL = 'que_typ_post_scoring_email';
    public const TYPE_REFUND_INTERIM_FEES = 'que_typ_refund_interim_fees';
    public const TYPE_CREATE_TASK = 'que_typ_create_task';

    protected $types = [
        self::TYPE_COMPANIES_HOUSE_COMPARE,
        self::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER,
        self::TYPE_CPID_EXPORT_CSV,
        self::TYPE_CONT_CHECKLIST,
        self::TYPE_TM_SNAPSHOT,
        self::TYPE_CPMS_REPORT_DOWNLOAD,
        self::TYPE_EBSR_REQUEST_MAP,
        self::TYPE_EBSR_PACK,
        self::TYPE_EBSR_PACK_FAILED,
        self::TYPE_SEND_MSI_RESPONSE,
        self::TYPE_EMAIL,
        self::TYPE_PRINT,
        self::TYPE_DISC_PRINTING,
        self::TYPE_DISC_PRINTING_PRINT,
        self::TYPE_PERMIT_GENERATE,
        self::TYPE_PERMIT_PRINT,
        self::TYPE_PERMIT_REPORT,
        self::TYPE_CREATE_GOODS_VEHICLE_LIST,
        self::TYPE_CREATE_PSV_VEHICLE_LIST,
        self::TYPE_UPDATE_NYSIIS_TM_NAME,
        self::TYPE_CNS,
        self::TYPE_CNS_EMAIL,
        self::TYPE_CREATE_COM_LIC,
        self::TYPE_REMOVE_DELETED_DOCUMENTS,
        self::TYPE_CREATE_CONTINUATION_SNAPSHOT,
        self::TYPE_CONT_DIGITAL_REMINDER,
        self::TYPE_IRHP_APPLICATION_PERMITS_ALLOCATE,
        self::TYPE_RUN_ECMT_SCORING,
        self::TYPE_ACCEPT_ECMT_SCORING,
        self::TYPE_COMM_LIC_BULK_REPRINT,
        self::TYPE_LETTER_BULK_UPLOAD,
        self::TYPE_EMAIL_BULK_UPLOAD,
        self::TYPE_POST_SCORING_EMAIL,
        self::TYPE_REFUND_INTERIM_FEES,
        self::TYPE_PERMITS_POST_SUBMIT,
        self::TYPE_CREATE_TASK,
    ];

    // Errors
    public const ERR_MAX_ATTEMPTS = 'Maximum attempts exceeded';

    /**
     * Increment attempts
     *
     * @return $this
     */
    public function incrementAttempts()
    {
        $curr = $this->getAttempts();
        $this->setAttempts(++$curr);
        return $this;
    }

    /**
     * Constructor
     *
     * @param RefData $messageType message type
     */
    public function __construct(RefData $messageType = null)
    {
        if (!is_null($messageType)) {
            $this->setType($messageType);
        }
    }

    /**
     * Validate queue
     *
     * @param string $type             type
     * @param string $status           status
     * @param string $processAfterDate string
     *
     * @return void
     * @throws ValidationException
     */
    public function validateQueue($type, $status, $processAfterDate)
    {
        if (!in_array($type, $this->types)) {
            throw new ValidationException(['error' => 'Unknown queue type']);
        }

        if (!in_array($status, $this->statuses)) {
            throw new ValidationException(['error' => 'Unknown queue status']);
        }

        //using not empty as potentially we could end up with empty string instead of null
        if (!empty($processAfterDate)) {
            $processAfterDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $processAfterDate);

            if (!$processAfterDateTime instanceof \DateTime) {
                throw new ValidationException(['error' => 'Queue process after date is not valid']);
            }
        }
    }
}
