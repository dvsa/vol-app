<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * SystemParameter Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="system_parameter")
 */
class SystemParameter extends AbstractSystemParameter
{
    public const CNS_EMAIL_LIST = 'CNS_EMAIL_LIST';
    public const CNS_EMAIL_LIST_CC = 'CNS_EMAIL_LIST_CC';
    public const DISABLED_SELFSERVE_CARD_PAYMENTS = 'DISABLED_SELFSERVE_CARD_PAYMENTS';
    public const SELFSERVE_USER_PRINTER = 'SELFSERVE_USER_PRINTER';
    public const RESOLVE_CARD_PAYMENTS_MIN_AGE = 'RESOLVE_CARD_PAYMENTS_MIN_AGE';
    public const DISABLE_GDS_VERIFY_SIGNATURES = 'DISABLE_GDS_VERIFY_SIGNATURES';
    public const DUPLICATE_VEHICLE_EMAIL_LIST = 'DUPLICATE_VEHICLE_EMAIL_LIST';
    public const PSV_REPORT_EMAIL_LIST = 'PSV_REPORT_EMAIL_LIST';
    public const INTERNATIONAL_GV_REPORT_EMAIL_TO = 'INTERNATIONAL_GV_REPORT_EMAIL_TO';
    public const INTERNATIONAL_GV_REPORT_EMAIL_CC = 'INTERNATIONAL_GV_REPORT_EMAIL_CC';
    public const DISABLE_DIGITAL_CONTINUATIONS = 'DISABLE_DIGITAL_CONTINUATIONS';
    public const DIGITAL_CONTINUATION_REMINDER_PERIOD = 'DIGITAL_CONT_REMINDER_PERIOD';
    public const DISABLE_DATA_RETENTION_DOCUMENT_DELETE = 'DISABLE_DR_DOCUMENT_DELETE';
    public const SYSTEM_DATA_RETENTION_USER = 'SYSTEM_DATA_RETENTION_USER';
    public const DR_DELETE_LIMIT = 'DR_DELETE_LIMIT';
    public const DISABLE_DATA_RETENTION_DELETE = 'DISABLE_DATA_RETENTION_DELETE';
    public const DISABLE_UK_COMMUNITY_LIC_OFFICE = 'DISABLE_UK_COMMUNITY_LIC_OFFICE';
    public const DISABLE_UK_COMMUNITY_LIC_REPRINT = 'DISABLE_UK_COMMUNITY_LIC_REPRINT';
    public const DISABLE_COM_LIC_BULK_REPRINT_DB = 'DISABLE_COM_LIC_BULK_REPRINT_DB';
    public const DISABLE_ECMT_ALLOC_EMAIL_NONE = 'DISABLE_ECMT_ALLOC_EMAIL_NONE';
    public const USE_ALT_ECMT_IOU_ALGORITHM = 'USE_ALT_ECMT_IOU_ALGORITHM';
    public const ENABLE_SELFSERVE_PROMPT = 'ENABLE_SELFSERVE_PROMPT';
    public const PERMITS_DAYS_TO_PAY_ISSUE_FEE = 'PERMITS_DAYS_TO_PAY_ISSUE_FEE';
    public const DATA_SEPARATION_TEAMS_EXEMPT = 'DATA_SEPARATION_TEAMS_EXEMPT';
    public const LAST_TM_NI_TASK_OWNER = 'LAST_TM_NI_TASK_OWNER';
    public const LAST_TM_GB_TASK_OWNER = 'LAST_TM_GB_TASK_OWNER';
    public const NEW_OP_EMAIL_GB = 'NEW_OP_EMAIL_GB';
    public const NEW_OP_EMAIL_NI = 'NEW_OP_EMAIL_NI';
}