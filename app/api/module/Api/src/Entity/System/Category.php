<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="category",
 *    indexes={
 *        @ORM\Index(name="ix_category_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_category_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_category_task_allocation_type", columns={"task_allocation_type"})
 *    }
 * )
 */
class Category extends AbstractCategory
{
    public const CATEGORY_LICENSING = 1;
    public const CATEGORY_COMPLIANCE = 2;
    public const CATEGORY_BUS_REGISTRATION = 3;
    public const CATEGORY_PERMITS = 4;
    public const CATEGORY_TRANSPORT_MANAGER = 5;
    public const CATEGORY_ENVIRONMENTAL = 7;
    public const CATEGORY_IRFO = 8;
    public const CATEGORY_APPLICATION = 9;
    public const CATEGORY_SUBMISSION = 10;
    public const CATEGORY_REPORT = 12;

    // @NOTE create constants for all sub categories as required. Only a subset
    // will ever be needed programatically so this list should be manageable
    public const TASK_SUB_CATEGORY_APPLICATION_ADDRESS_CHANGE_DIGITAL = 3;
    public const TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE = 11;
    public const TASK_SUB_CATEGORY_APPLICATION_FORMS_ASSISTED_DIGITAL = 14;
    public const TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL = 15;
    public const TASK_SUB_CATEGORY_APPLICATION_SUBSIDIARY_DIGITAL = 25;
    public const TASK_SUB_CATEGORY_APPLICATION_TIME_EXPIRED = 30;
    public const TASK_SUB_CATEGORY_APPLICATION_SURRENDER = 201;

    public const TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FIRST_REQUEST = 22;
    public const TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FINAL_REQUEST = 23;

    public const TASK_SUB_CATEGORY_HEARINGS_APPEALS = 49;
    public const TASK_SUB_CATEGORY_DECISION = 96;
    public const TASK_SUB_CATEGORY_RECOMMENDATION = 97;
    public const TASK_SUB_CATEGORY_ASSIGNMENT = 114;
    public const TASK_SUB_CATEGORY_REVIEW_COMPLAINT = 61;
    public const TASK_SUB_CATEGORY_LICENSING_GENERAL_TASK = 77;
    public const TASK_SUB_CATEGORY_INSPECTION_REQUEST_SEMINAR = 78;
    public const TASK_SUB_CATEGORY_CHANGE_TO_TAXI_PHV_DIGITAL = 165;
    public const TASK_SUB_CATEGORY_BUSINESS_DETAILS_CHANGE = 169;
    public const TASK_SUB_CATEGORY_SUR_41_ASSISTED_DIGITAL = 82;
    public const TASK_SUB_CATEGORY_NR = 47;
    public const TASK_SUB_CATEGORY_TM_PERIOD_OF_GRACE = 84;
    public const TASK_SUB_CATEGORY_DIRECTOR_CHANGE_DIGITAL = 10;
    public const TASK_SUB_CATEGORY_PARTNER_CHANGE_DIGITAL = 7;
    public const TASK_SUB_CATEGORY_PERSON_CHANGE_DIGITAL = 194;
    public const TASK_SUB_CATEGORY_APPLICATION_TM1_DIGITAL = 28;
    public const TASK_SUB_CATEGORY_APPLICATION_TM1_REMOVAL_VARIATION = 191;
    public const TASK_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS = 74;
    public const TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK = 95;

    public const SCAN_SUB_CATEGORY_CHANGE_OF_ENTITY = 85;

    public const DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL = 5;
    public const DOC_SUB_CATEGORY_LICENCE_VEHICLE_LIST = 91;
    public const DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL = 112;
    public const DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION = 98;
    public const DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE = 99;
    public const DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL = 100;
    public const DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK = 216;
    public const DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_ASSISTED_DIGITAL = 12;
    public const DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL = 13;
    public const DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS = 74;
    public const DOC_SUB_CATEGORY_OTHER_DOCUMENTS = 79;
    public const DOC_SUB_CATEGORY_FEE_REQUEST = 110;
    public const DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE = 74;
    public const DOC_SUB_CATEGORY_CPID = 170;
    public const DOC_SUB_CATEGORY_DISCS = 166;
    public const DOC_SUB_CATEGORY_FINANCIAL_REPORTS = 180;
    public const DOC_SUB_CATEGORY_NR = 53;

    public const BUS_SUB_CATEGORY_OTHER_DOCUMENTS = 40;
    public const BUS_SUB_CATEGORY_TRANSXCHANGE_ZIP = 107;
    public const BUS_SUB_CATEGORY_TRANSXCHANGE_PDF = 108;

    public const SUBMISSION_SUB_CATEGORY_OTHER = 146;
}
