<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefData Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="ref_data",
 *    indexes={
 *        @ORM\Index(name="ix_ref_data_parent_id", columns={"parent_id"}),
 *        @ORM\Index(name="ix_ref_data_ref_data_category_id", columns={"ref_data_category_id"})
 *    }
 * )
 */
class RefData extends AbstractRefData
{
    public const FEE_TYPE_APP = 'APP';
    public const FEE_TYPE_VAR = 'VAR';
    public const FEE_TYPE_GRANT = 'GRANT';
    public const FEE_TYPE_CONT = 'CONT';
    public const FEE_TYPE_VEH = 'VEH';
    public const FEE_TYPE_GRANTINT = 'GRANTINT';
    public const FEE_TYPE_INTVEH = 'INTVEH';
    public const FEE_TYPE_DUP = 'DUP';
    public const FEE_TYPE_ANN = 'ANN';
    public const FEE_TYPE_GRANTVAR = 'GRANTVAR';
    public const FEE_TYPE_BUSAPP = 'BUSAPP';
    public const FEE_TYPE_BUSVAR = 'BUSVAR';
    public const FEE_TYPE_GVANNVEH = 'GVANNVEH';
    public const FEE_TYPE_INTUPGRADEVEH = 'INTUPGRADEVEH';
    public const FEE_TYPE_INTAMENDED = 'INTAMENDED';
    public const FEE_TYPE_IRFOPSVAPP = 'IRFOPSVAPP';
    public const FEE_TYPE_IRFOPSVANN = 'IRFOPSVANN';
    public const FEE_TYPE_IRFOPSVCOPY = 'IRFOPSVCOPY';
    public const FEE_TYPE_IRFOGVPERMIT = 'IRFOGVPERMIT';

    public const TASK_ACTION_DATE_TODAY = 'tdt_today';

    public const SIG_PHYSICAL_SIGNATURE = 'sig_physical_signature';
    public const SIG_DIGITAL_SIGNATURE = 'sig_digital_signature';
    public const SIG_SIGNATURE_NOT_REQUIRED = 'sig_signature_not_required';

    public const PHONE_NUMBER_PRIMARY_TYPE = 'phone_t_primary';
    public const PHONE_NUMBER_SECONDARY_TYPE = 'phone_t_secondary';

    public const LICENCE_STATUS = 'lic_status';

    public const PERMIT_APP_STATUS_CANCELLED = 'permit_app_cancelled';
    public const PERMIT_APP_STATUS_NOT_YET_SUBMITTED = 'permit_app_nys';
    public const PERMIT_APP_STATUS_UNDER_CONSIDERATION = 'permit_app_uc';
    public const PERMIT_APP_STATUS_WITHDRAWN = 'permit_app_withdrawn';
    public const PERMIT_APP_STATUS_AWAITING_FEE = 'permit_app_awaiting';
    public const PERMIT_APP_STATUS_FEE_PAID = 'permit_app_fee_paid';
    public const PERMIT_APP_STATUS_UNSUCCESSFUL = 'permit_app_unsuccessful';
    public const PERMIT_APP_STATUS_ISSUING = 'permit_app_issuing';
    public const PERMIT_APP_STATUS_VALID = 'permit_app_valid';

    //Surrenders
    public const SURRENDER_STATUS_START = 'surr_sts_start';
    public const SURRENDER_STATUS_CONTACTS_COMPLETE = 'surr_sts_contacts_complete';
    public const SURRENDER_STATUS_DISCS_COMPLETE = 'surr_sts_discs_complete';
    public const SURRENDER_STATUS_LIC_DOCS_COMPLETE = 'surr_sts_lic_docs_complete';
    public const SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE = 'surr_sts_comm_lic_docs_complete';
    public const SURRENDER_STATUS_DETAILS_CONFIRMED = 'surr_sts_details_confirmed';
    public const SURRENDER_STATUS_SUBMITTED = 'surr_sts_submitted';
    public const SURRENDER_STATUS_SIGNED = 'surr_sts_signed';
    public const SURRENDER_STATUS_WITHDRAWN = 'surr_sts_withdrawn';
    public const SURRENDER_STATUS_APPROVED = 'surr_sts_approved';
    public const SURRENDER_DOC_STATUS_DESTROYED = 'doc_sts_destroyed';
    public const SURRENDER_DOC_STATUS_LOST = 'doc_sts_lost';
    public const SURRENDER_DOC_STATUS_STOLEN = 'doc_sts_stolen';

    // Report
    public const REPORT_TYPE_COMM_LIC_BULK_REPRINT = 'rep_typ_comm_lic_bulk_reprint';
    public const REPORT_TYPE_BULK_EMAIL = 'rep_typ_bulk_email';
    public const REPORT_TYPE_BULK_LETTER = 'rep_typ_bulk_letter';
    public const REPORT_TYPE_POST_SCORING_EMAIL = 'rep_typ_post_scoring_email';

    public const EMISSIONS_CATEGORY_EURO6_REF = 'emissions_cat_euro6';
    public const EMISSIONS_CATEGORY_EURO5_REF = 'emissions_cat_euro5';
    public const EMISSIONS_CATEGORY_NA_REF = 'emissions_cat_na';

    // Business process
    public const BUSINESS_PROCESS_APG = 'app_business_process_apg';
    public const BUSINESS_PROCESS_APGG = 'app_business_process_apgg';
    public const BUSINESS_PROCESS_APSG = 'app_business_process_apsg';
    public const BUSINESS_PROCESS_AG = 'app_business_process_ag';

    // International journeys
    public const INTER_JOURNEY_LESS_60 = 'inter_journey_less_60';
    public const INTER_JOURNEY_60_90 = 'inter_journey_60_90';
    public const INTER_JOURNEY_MORE_90 = 'inter_journey_more_90';

    // journey
    public const JOURNEY_SINGLE = 'journey_single';
    public const JOURNEY_MULTIPLE = 'journey_multiple';

    // standard/cabotage
    public const STD_OR_CAB_STANDARD = 'std_or_cab_standard';
    public const STD_OR_CAB_CABOTAGE = 'std_or_cab_cabotage';

    // permit categories for morocco bilaterals
    public const PERMIT_CAT_STANDARD_MULTIPLE_15 = 'permit_cat_standard_multiple_15';
    public const PERMIT_CAT_STANDARD_SINGLE = 'permit_cat_standard_single';
    public const PERMIT_CAT_EMPTY_ENTRY = 'permit_cat_empty_entry';
    public const PERMIT_CAT_HORS_CONTINGENT = 'permit_cat_hors_contingent';

    // ecmt permit usage (three options)
    public const ECMT_PERMIT_USAGE_THREE_BOTH = 'st_permit_usage_both';
    public const ECMT_PERMIT_USAGE_THREE_CROSS_TRADE_ONLY = 'st_permit_usage_cross_trade_only';
    public const ECMT_PERMIT_USAGE_THREE_TRANSIT_ONLY = 'st_permit_usage_transit_only';

    // ecmt permit usage (four options)
    public const ECMT_PERMIT_USAGE_FOUR_CROSS_TRADE_ONLY = 'ecmt_per_usa_4_cross_trade_only';
    public const ECMT_PERMIT_USAGE_FOUR_NON_EU_ONLY = 'ecmt_per_usa_4_non_eu_only';
    public const ECMT_PERMIT_USAGE_FOUR_ECMT_WITHOUT = 'ecmt_per_usa_4_ecmt_without';
    public const ECMT_PERMIT_USAGE_FOUR_ALL_JOURNEYS = 'ecmt_per_usa_4_all_journeys';

    //Grant authorities
    public const GRANT_AUTHORITY_DELEGATED = 'grant_authority_dl';
    public const GRANT_AUTHORITY_TC = 'grant_authority_tc';
    public const GRANT_AUTHORITY_TR = 'grant_authority_tr';

    //Application vehicle types
    public const APP_VEHICLE_TYPE_MIXED = 'app_veh_type_mixed';
    public const APP_VEHICLE_TYPE_LGV = 'app_veh_type_lgv';
    public const APP_VEHICLE_TYPE_HGV = 'app_veh_type_hgv';
    public const APP_VEHICLE_TYPE_PSV = 'app_veh_type_psv';

    /**
     * Transport Manager Application
     */
    public const TMA_SIGN_AS_TM = 'tma_sign_as_tm';
    public const TMA_SIGN_AS_OP = 'tma_sign_as_op';
    public const TMA_SIGN_AS_TM_OP = 'tma_sign_as_top';

    /**
     * Journeys
     */
    public const JOURNEY_NEW_APPLICATION = 'jrny_new_application';
    public const JOURNEY_CONTINUATION = 'jrny_continuation';
    public const JOURNEY_VARIATION = 'jrny_variation';
    public const JOURNEY_TM_APPLICATION = 'jrny_tm_application';
    public const JOURNEY_SURRENDER = 'jrny_surrender';

    /**
     * RefData constructor.
     *
     * @param string $id Refdata ID
     */
    public function __construct($id = null)
    {
        if (!is_null($id)) {
            $this->setId($id);
        }
    }
}
