<?php

namespace Common;

/**
 * Holds the Ref Data constants required by the web app
 */
class RefData
{
    public const LICENCE_TYPE_RESTRICTED = 'ltyp_r';

    public const LICENCE_TYPE_STANDARD_INTERNATIONAL = 'ltyp_si';

    public const LICENCE_TYPE_STANDARD_NATIONAL = 'ltyp_sn';

    public const LICENCE_TYPE_SPECIAL_RESTRICTED = 'ltyp_sr';

    public const CASE_TYPE_LICENCE = 'case_t_lic';

    public const CASE_TYPE_APPLICATION = 'case_t_app';

    public const CASE_TYPE_TM = 'case_t_tm';

    public const CASE_TYPE_IMPOUNDING = 'case_t_imp';

    public const FEE_PAYMENT_METHOD_CARD_ONLINE  = 'fpm_card_online';

    public const FEE_PAYMENT_METHOD_CARD_OFFLINE = 'fpm_card_offline';

    public const FEE_PAYMENT_METHOD_CASH         = 'fpm_cash';

    public const FEE_PAYMENT_METHOD_CHEQUE       = 'fpm_cheque';

    public const FEE_PAYMENT_METHOD_POSTAL_ORDER = 'fpm_po';

    public const FEE_PAYMENT_METHOD_WAIVE        = 'fpm_waive';

    public const FEE_STATUS_OUTSTANDING       = 'lfs_ot';

    public const FEE_STATUS_PAID              = 'lfs_pd';

    public const FEE_STATUS_CANCELLED         = 'lfs_cn';

    public const FEE_TYPE_CONT = 'CONT';

    public const TRANSACTION_STATUS_COMPLETE    = 'pay_s_pd';

    public const TRANSACTION_STATUS_OUTSTANDING = 'pay_s_os';

    public const TRANSACTION_STATUS_CANCELLED   = 'pay_s_cn';

    public const TRANSACTION_STATUS_FAILED      = 'pay_s_fail';

    public const TRANSACTION_STATUS_PAID        = 'pay_s_pd';

    public const TRANSACTION_TYPE_WAIVE    = 'trt_waive';

    public const TRANSACTION_TYPE_PAYMENT  = 'trt_payment';

    public const TRANSACTION_TYPE_REFUND   = 'trt_refund';

    public const TRANSACTION_TYPE_REVERSAL = 'trt_reversal';

    public const TRANSACTION_TYPE_OTHER    = 'trt_other';


    /**
     * Goods or PSV keys
     */
    public const LICENCE_CATEGORY_GOODS_VEHICLE = 'lcat_gv';

    public const LICENCE_CATEGORY_PSV = 'lcat_psv';

    /**
     * Licence statuses
     */
    public const LICENCE_STATUS_UNDER_CONSIDERATION = 'lsts_consideration';

    public const LICENCE_STATUS_NOT_SUBMITTED = 'lsts_not_submitted';

    public const LICENCE_STATUS_SUSPENDED = 'lsts_suspended';

    public const LICENCE_STATUS_VALID = 'lsts_valid';

    public const LICENCE_STATUS_CURTAILED = 'lsts_curtailed';

    public const LICENCE_STATUS_GRANTED = 'lsts_granted';

    public const LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION = 'lsts_surr_consideration';

    public const LICENCE_STATUS_SURRENDERED = 'lsts_surrendered';

    public const LICENCE_STATUS_WITHDRAWN = 'lsts_withdrawn';

    public const LICENCE_STATUS_REFUSED = 'lsts_refused';

    public const LICENCE_STATUS_REVOKED = 'lsts_revoked';

    public const LICENCE_STATUS_NOT_TAKEN_UP = 'lsts_ntu';

    public const LICENCE_STATUS_TERMINATED = 'lsts_terminated';

    public const LICENCE_STATUS_CONTINUATION_NOT_SOUGHT = 'lsts_cns';

    public const LICENCE_STATUS_UNLICENSED = 'lsts_unlicenced';

    public const LICENCE_STATUS_CONSIDERATION = 'lsts_consideration';

    public const LICENCE_STATUS_CANCELLED = 'lsts_cancelled';

    /**
     * Journeys
     */
    public const JOURNEY_NEW_APPLICATION = 'jrny_new_application';

    public const JOURNEY_CONTINUATION = 'jrny_continuation';

    public const JOURNEY_VARIATION = 'jrny_variation';

    public const JOURNEY_TM_APPLICATION = 'jrny_tm_application';

    public const JOURNEY_SURRENDER = 'jrny_surrender';

    /**
     * Application statuses
     */
    public const APPLICATION_STATUS_NOT_SUBMITTED = 'apsts_not_submitted';

    // this status will be displayed everywhere as Awaiting grant fee as per OLCS-12606
    public const APPLICATION_STATUS_GRANTED = 'apsts_granted';

    public const APPLICATION_STATUS_UNDER_CONSIDERATION = 'apsts_consideration';

    // this status will be displayed everywhere as Granted as per OLCS-12606
    public const APPLICATION_STATUS_VALID = 'apsts_valid';

    public const APPLICATION_STATUS_WITHDRAWN = 'apsts_withdrawn';

    public const APPLICATION_STATUS_REFUSED = 'apsts_refused';

    public const APPLICATION_STATUS_NOT_TAKEN_UP = 'apsts_ntu';

    public const APPLICATION_STATUS_CANCELLED = 'apsts_cancelled';

    /**
     * Application withdraw reasons
     */
    public const APPLICATION_WITHDRAW_REASON_WITHDRAWN = 'withdrawn';

    /**
     * Variation section statuses
     */
    public const VARIATION_STATUS_UNCHANGED = 0;

    public const VARIATION_STATUS_REQUIRES_ATTENTION = 1;

    public const VARIATION_STATUS_UPDATED = 2;

    /**
     * Variation types
     */
    public const VARIATION_TYPE_DIRECTOR_CHANGE = 'vtyp_director_change';

    /**
     * Grant authorities
     */
    public const GRANT_AUTHORITY_DELEGATED = 'grant_authority_dl';

    public const GRANT_AUTHORITY_TC = 'grant_authority_tc';

    public const GRANT_AUTHORITY_TR = 'grant_authority_tr';

    /**
     * Transport Manager Application
     */
    public const TMA_SIGN_AS_TM = 'tma_sign_as_tm';

    public const TMA_SIGN_AS_OP = 'tma_sign_as_op';

    public const TMA_SIGN_AS_TM_OP = 'tma_sign_as_top';

    public const TMA_STATUS_INCOMPLETE = 'tmap_st_incomplete';

    public const TMA_STATUS_AWAITING_SIGNATURE = 'tmap_st_awaiting_signature';

    public const TMA_STATUS_TM_SIGNED = 'tmap_st_tm_signed';

    public const TMA_STATUS_OPERATOR_SIGNED = 'tmap_st_operator_signed';

    public const TMA_STATUS_POSTAL_APPLICATION = 'tmap_st_postal_application';

    public const TMA_STATUS_RECEIVED = 'tmap_st_received';

    public const TMA_STATUS_DETAILS_SUBMITTED = 'tmap_st_details_submitted';

    public const TMA_STATUS_DETAILS_CHECKED = 'tmap_st_details_checked';

    public const TMA_STATUS_OPERATOR_APPROVED = 'tmap_st_operator_approved';

    /**
     * Condition and Undertakings
     */
    public const ATTACHED_TO_LICENCE = 'cat_lic';

    public const ATTACHED_TO_OPERATING_CENTRE = 'cat_oc';

    public const ADDED_VIA_CASE = 'cav_case';

    public const ADDED_VIA_LICENCE = 'cav_lic';

    public const ADDED_VIA_APPLICATION = 'cav_app';

    public const TYPE_CONDITION = 'cdt_con';

    public const TYPE_UNDERTAKING = 'cdt_und';

    /**
     * Organisation types
     */
    public const ORG_TYPE_REGISTERED_COMPANY = 'org_t_rc';

    public const ORG_TYPE_SOLE_TRADER = 'org_t_st';

    public const ORG_TYPE_LLP = 'org_t_llp';

    public const ORG_TYPE_PARTNERSHIP = 'org_t_p';

    public const ORG_TYPE_OTHER = 'org_t_pa';

    public const ORG_TYPE_IRFO = 'org_t_ir';

    public const ORG_TYPE_RC = 'org_t_rc';

    /**
     * Schedule41
     */
    public const S4_STATUS_APPROVED = 's4_sts_approved';

    public const S4_STATUS_REFUSED = 's4_sts_refused';

    /**
     * Bus Reg Status
     */
    public const BUSREG_STATUS_NEW = 'breg_s_new';

    public const BUSREG_STATUS_VARIATION = 'breg_s_var';

    public const BUSREG_STATUS_CANCELLATION = 'breg_s_cancellation';

    public const BUSREG_STATUS_ADMIN = 'breg_s_admin';

    public const BUSREG_STATUS_REGISTERED = 'breg_s_registered';

    public const BUSREG_STATUS_REFUSED = 'breg_s_refused';

    public const BUSREG_STATUS_WITHDRAWN = 'breg_s_withdrawn';

    public const BUSREG_STATUS_CNS = 'breg_s_cns';

    public const BUSREG_STATUS_CANCELLED = 'breg_s_cancelled';

    /**
     * EBSR Status
     */
    public const EBSR_STATUS_PROCESSED = 'ebsrs_processed';

    public const EBSR_STATUS_PROCESSING = 'ebsrs_processing';

    public const EBSR_STATUS_VALIDATING = 'ebsrs_validating';

    public const EBSR_STATUS_SUBMITTED = 'ebsrs_submitted';

    public const EBSR_STATUS_FAILED = 'ebsrs_failed';

    /**
     * Role permissions
     */
    public const PERMISSION_SELFSERVE_PARTNER_ADMIN = 'partner-admin';

    public const PERMISSION_SELFSERVE_PARTNER_USER = 'partner-user';

    public const PERMISSION_SELFSERVE_LVA = 'selfserve-lva';

    public const PERMISSION_SELFSERVE_TM_DASHBOARD = 'selfserve-tm-dashboard';

    public const PERMISSION_SELFSERVE_DASHBOARD = 'selfserve-nav-dashboard';

    public const PERMISSION_SYSTEM_ADMIN = 'system-admin';

    public const PERMISSION_INTERNAL_ADMIN = 'internal-admin';

    public const PERMISSION_INTERNAL_IRHP_ADMIN = 'internal-irhp-admin';

    public const PERMISSION_INTERNAL_EDIT = 'internal-edit';

    public const PERMISSION_INTERNAL_VIEW = 'internal-view';

    public const PERMISSION_INTERNAL_CASE = 'internal-case';

    public const PERMISSION_INTERNAL_USER = 'internal-user';

    public const PERMISSION_INTERNAL_DOCUMENTS = 'internal-documents';

    public const PERMISSION_INTERNAL_NOTES = 'internal-notes';

    public const PERMISSION_INTERNAL_PERMITS = 'internal-permits';

    public const PERMISSION_INTERNAL_PUBLICATIONS = 'internal-publications';

    public const PERMISSION_INTERNAL_LIMITED_READ_ONLY = 'internal-limited-read-only';

    public const PERMISSION_INTERNAL_FEES = 'internal-fees';

    public const PERMISSION_INTERNAL_OPPOSITION = 'internal-opposition';

    public const PERMISSION_INTERNAL_PROCESSING = 'internal-processing';

    public const PERMISSION_CAN_MANAGE_USER_INTERNAL = 'can-manage-user-internal';

    public const PERMISSION_CAN_MANAGE_USER_SELFSERVE = 'can-manage-user-selfserve';

    public const PERMISSION_SELFSERVE_EBSR_UPLOAD = 'selfserve-ebsr-upload';

    public const PERMISSION_SELFSERVE_EBSR_DOCUMENTS = 'selfserve-ebsr-documents';
    public const PERMISSION_CAN_LIST_CONVERSATIONS = 'can-list-conversations';
    public const PERMISSION_CAN_LIST_MESSAGES = 'can-list-messages';
    public const PERMISSION_CAN_REPLY_TO_CONVERSATION = 'can-reply-to-conversation';
    public const PERMISSION_CAN_CREATE_CONVERSATION = 'can-create-conversation';
    public const PERMISSION_CAN_DISABLE_MESSAGING = 'can-disable-messaging';
    public const PERMISSION_CAN_ENABLE_MESSAGING = 'can-enable-messaging';
    public const PERMISSION_CAN_CLOSE_CONVERSATION = 'can-close-conversation';

    /**
     * User Roles
     */
    public const ROLE_INTERNAL_LIMITED_READ_ONLY = 'internal-limited-read-only';

    public const ROLE_INTERNAL_READ_ONLY = 'internal-read-only';

    public const ROLE_INTERNAL_CASE_WORKER = 'internal-case-worker';

    public const ROLE_INTERNAL_ADMIN = 'internal-admin';

    public const ROLE_SYSTEM_ADMIN = 'system-admin';

    public const ROLE_INTERNAL_IRHP_ADMIN = 'internal-irhp-admin';

    public const ROLE_OPERATOR_TC = 'operator-tc';

    public const ROLE_OPERATOR_ADMIN = 'operator-admin';

    public const ROLE_OPERATOR_USER = 'operator-user';

    public const ROLE_OPERATOR_TM = 'operator-tm';

    public const ROLE_PARTNER_ADMIN = 'partner-admin';

    public const ROLE_PARTNER_USER = 'partner-user';

    public const ROLE_LOCAL_AUTHORITY_ADMIN = 'local-authority-admin';

    public const ROLE_LOCAL_AUTHORITY_USER = 'local-authority-user';

    public const ROLE_ANON = 'anon';

    /**
     * User types
     */
    public const USER_TYPE_INTERNAL = 'internal';

    public const USER_TYPE_LOCAL_AUTHORITY = 'local-authority';

    public const USER_TYPE_PARTNER = 'partner';

    public const USER_TYPE_TM = 'transport-manager';

    public const USER_TYPE_OPERATOR = 'operator';

    /**
     * Operator CPID.
     */
    public const OPERATOR_CPID_CENTRAL = 'op_cpid_central_government';

    public const OPERATOR_CPID_LOCAL = 'op_cpid_local_government';

    public const OPERATOR_CPID_CORPORATION = 'op_cpid_public_corporation';

    public const OPERATOR_CPID_DEFAULT = 'op_cpid_default';

    public const OPERATOR_CPID_ALL = 'op_cpid_all';

    /**
     * Contact Details
     */
    public const TRANSPORT_MANAGER_TYPE_EXTERNAL = 'tm_t_e';

    public const TRANSPORT_MANAGER_TYPE_BOTH = 'tm_t_b';

    public const TRANSPORT_MANAGER_STATUS_CURRENT = 'tm_s_cur';

    public const TRANSPORT_MANAGER_STATUS_DISQUALIFIED = 'tm_s_dis';

    public const TRANSPORT_MANAGER_STATUS_REMOVED = 'tm_s_rem';

    public const CONTACT_TYPE_PARTNER = 'ct_partner';

    public const CONTACT_TYPE_REGISTERED = 'ct_reg';

    /**
     * IRFO Stock Control
     */
    public const IRFO_STOCK_CONTROL_STATUS_IN_STOCK = 'irfo_perm_s_s_in_stock';

    public const IRFO_STOCK_CONTROL_STATUS_ISSUED = 'irfo_perm_s_s_issued';

    public const IRFO_STOCK_CONTROL_STATUS_VOID = 'irfo_perm_s_s_void';

    public const IRFO_STOCK_CONTROL_STATUS_RETURNED = 'irfo_perm_s_s_ret';

    // PSV Vehicle sizes
    public const PSV_VEHICLE_SIZE_SMALL = 'psvvs_small';

    public const PSV_VEHICLE_SIZE_MEDIUM_LARGE = 'psvvs_medium_large';

    public const PSV_VEHICLE_SIZE_BOTH = 'psvvs_both';

    /**
     * IRFO Status
     */
    public const IRFO_PSV_AUTH_STATUS_APPROVED = 'irfo_auth_s_approved';

    public const IRFO_PSV_AUTH_STATUS_CNS = 'irfo_auth_s_cns';

    public const IRFO_PSV_AUTH_STATUS_GRANTED = 'irfo_auth_s_granted';

    public const IRFO_PSV_AUTH_STATUS_PENDING = 'irfo_auth_s_pending';

    public const IRFO_PSV_AUTH_STATUS_REFUSED = 'irfo_auth_s_refused';

    public const IRFO_PSV_AUTH_STATUS_RENEW = 'irfo_auth_s_renew';

    public const IRFO_PSV_AUTH_STATUS_WITHDRAWN = 'irfo_auth_s_withdrawn';

    /**
     * Applied VIA
     */
    public const APPLIED_VIA_POST = 'applied_via_post';

    public const APPLIED_VIA_PHONE = 'applied_via_phone';

    public const APPLIED_VIA_SELFSERVE = 'applied_via_selfserve';

    /**
     * Impounding types
     */
    public const IMPOUNDING_TYPE_HEARING = 'impt_hearing';

    public const IMPOUNDING_TYPE_PAPER = 'impt_paper';

    /**
     * Convictions
     */
    public const CONVICTION_CATEGORY_USER_DEFINED = 'conv_c_cat_1144';

    public const NORTHERN_IRELAND_TRAFFIC_AREA_CODE = 'N';

    public const COMMUNITY_LICENCE_STATUS_PENDING = 'cl_sts_pending';

    public const COMMUNITY_LICENCE_STATUS_ACTIVE = 'cl_sts_active';

    public const COMMUNITY_LICENCE_STATUS_EXPIRED = 'cl_sts_expired';

    public const COMMUNITY_LICENCE_STATUS_WITHDRAWN = 'cl_sts_withdrawn';

    public const COMMUNITY_LICENCE_STATUS_SUSPENDED = 'cl_sts_suspended';

    public const COMMUNITY_LICENCE_STATUS_VOID = 'cl_sts_annulled';

    public const COMMUNITY_LICENCE_STATUS_RETURNDED = 'cl_sts_returned';

    /**
     * Erru
     */
    public const ERRU_RESPONSE_SENT = 'erru_case_t_msirs';

    public const ERRU_RESPONSE_SENDING_FAILED = 'erru_case_t_msirsf';

    public const ERRU_RESPONSE_QUEUED = 'erru_case_t_msirnys';

    public const ERROR_FEE_NOT_CREATED = 'AP-FEE-NOT-CREATED';

    public const UNDERTAKINGS_KEY = 'undertakings';

    public const SIGNATURE_TYPE_PHYSICAL_SIGNATURE = 'sig_physical_signature';

    public const SIGNATURE_TYPE_DIGITAL_SIGNATURE = 'sig_digital_signature';

    public const SIGNATURE_TYPE_NOT_REQUIRED = 'sig_signature_not_required';

    public const ERR_NO_FEES = 'ERR_NO_FEES';

    public const ERR_WAIT = 'ERR_WAIT';

    public const AD_POST = 0;

    public const AD_UPLOAD_NOW = 1;

    public const AD_UPLOAD_LATER = 2;

    public const PHONE_TYPE_PRIMARY = 'phone_t_primary';

    public const PHONE_TYPE_SECONDARY = 'phone_t_secondary';

    public const CONTINUATIONS_DISPLAY_PERSON_COUNT = 10;

    public const CONTINUATIONS_DISPLAY_VEHICLES_COUNT = 10;

    public const CONTINUATIONS_DISPLAY_USERS_COUNT = 10;

    public const CONTINUATIONS_DISPLAY_OPERATING_CENTRES_COUNT = 10;

    public const CONTINUATIONS_DISPLAY_SAFETY_INSPECTORS_COUNT = 10;

    public const CONTINUATIONS_DISPLAY_TM_COUNT = 10;

    public const LICENCE_CHECKLIST_TYPE_OF_LICENCE = 'type_of_licence';

    public const LICENCE_CHECKLIST_BUSINESS_TYPE = 'business_type';

    public const LICENCE_CHECKLIST_BUSINESS_DETAILS = 'business_details';

    public const LICENCE_CHECKLIST_ADDRESSES = 'addresses';

    public const LICENCE_CHECKLIST_PEOPLE = 'people';

    public const LICENCE_CHECKLIST_VEHICLES = 'vehicles';

    public const LICENCE_CHECKLIST_USERS = 'users';

    public const LICENCE_CHECKLIST_OPERATING_CENTRES = 'operating_centres';

    public const LICENCE_CHECKLIST_OPERATING_CENTRES_AUTHORITY = 'operating_centres_authority';

    public const LICENCE_CHECKLIST_TRANSPORT_MANAGERS = 'transport_managers';

    public const LICENCE_CHECKLIST_SAFETY_INSPECTORS = 'safety';

    public const LICENCE_CHECKLIST_SAFETY_DETAILS = 'safety_details';

    public const LICENCE_SAFETY_INSPECTOR_EXTERNAL = 'tach_external';

    public const RESULT_LICENCE_CONTINUED = 'licence_continued';

    public const CONTINUATION_STATUS_GENERATED = 'con_det_sts_printed';

    public const CONTINUATION_STATUS_COMPLETE = 'con_det_sts_complete';

    public const PTR_ACTION_TO_BE_TAKEN_REVOKE = 'ptr_action_to_be_taken_revoke';

    public const PTR_ACTION_TO_BE_TAKEN_PI = 'ptr_action_to_be_taken_pi';

    public const PTR_ACTION_TO_BE_TAKEN_WARNING = 'ptr_action_to_be_taken_warning';

    public const PTR_ACTION_TO_BE_TAKEN_NFA = 'ptr_action_to_be_taken_nfa';

    public const PTR_ACTION_TO_BE_TAKEN_OTHER = 'ptr_action_to_be_taken_other';

    /**
     * Permit statuses
     */
    public const PERMIT_VALID = 'permit_valid';

    public const PERMIT_EXPIRED = 'permit_expired';

    public const PERMIT_AWAITING = 'permit_awaiting';

    public const PERMIT_NYS = 'permit_nys';

    /**
     * ECMT Permit application statuses
     */
    public const PERMIT_APP_STATUS_CANCELLED = 'permit_app_cancelled';

    public const PERMIT_APP_STATUS_NOT_YET_SUBMITTED = 'permit_app_nys';

    public const PERMIT_APP_STATUS_UNDER_CONSIDERATION = 'permit_app_uc';

    public const PERMIT_APP_STATUS_WITHDRAWN = 'permit_app_withdrawn';

    public const PERMIT_APP_STATUS_AWAITING_FEE = 'permit_app_awaiting';

    public const PERMIT_APP_STATUS_FEE_PAID = 'permit_app_fee_paid';

    public const PERMIT_APP_STATUS_UNSUCCESSFUL = 'permit_app_unsuccessful';

    public const PERMIT_APP_STATUS_ISSUING = 'permit_app_issuing';

    public const PERMIT_APP_STATUS_VALID = 'permit_app_valid';

    public const PERMIT_APP_STATUS_EXPIRED = 'permit_app_expired';

    public const PERMIT_APP_WITHDRAW_REASON_UNPAID = 'permits_app_withdraw_not_paid';

    public const PERMIT_APP_WITHDRAW_REASON_DECLINED = 'permits_app_withdraw_declined';

    public const PERMIT_APP_WITHDRAW_REASON_USER = 'permits_app_withdraw_by_user';

    /**
     * ECMT Permit application international journey percentages
     */
    public const ECMT_APP_JOURNEY_LESS_60 = 'inter_journey_less_60';

    public const ECMT_APP_JOURNEY_60_90 = 'inter_journey_60_90';

    public const ECMT_APP_JOURNEY_OVER_90 = 'inter_journey_more_90';

    public const IRHP_PERMIT_STATUS_PENDING            = 'irhp_permit_pending';

    public const IRHP_PERMIT_STATUS_AWAITING_PRINTING  = 'irhp_permit_awaiting_printing';

    public const IRHP_PERMIT_STATUS_PRINTING           = 'irhp_permit_printing';

    public const IRHP_PERMIT_STATUS_PRINTED            = 'irhp_permit_printed';

    public const IRHP_PERMIT_STATUS_ERROR              = 'irhp_permit_error';

    public const IRHP_PERMIT_STATUS_CEASED             = 'irhp_permit_ceased';

    public const IRHP_PERMIT_STATUS_TERMINATED         = 'irhp_permit_terminated';

    public const IRHP_PERMIT_STATUS_EXPIRED            = 'irhp_permit_expired';

    /**
     * ECMT Permit application sources
     */
    public const ECMT_APP_SOURCE_SELFSERVE = 'app_source_selfserve';

    public const ECMT_APP_SOURCE_INTERNAL = 'app_source_internal';

    //feature toggle statuses
    public const FT_ACTIVE = 'always-active';

    public const FT_INACTIVE = 'inactive';

    public const FT_CONDITIONAL = 'conditionally-active';

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

    //IRHP Permit Type
    public const ECMT_PERMIT_TYPE_ID = 1;

    public const ECMT_SHORT_TERM_PERMIT_TYPE_ID = 2;

    public const ECMT_REMOVAL_PERMIT_TYPE_ID = 3;

    public const IRHP_BILATERAL_PERMIT_TYPE_ID = 4;

    public const IRHP_MULTILATERAL_PERMIT_TYPE_ID = 5;

    public const CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID = 6;

    public const CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID = 7;

    // IRHP Permit Fee Types
    public const IRHP_GV_APPLICATION_FEE_TYPE = 'IRHPGVAPP';

    public const IRHP_GV_ISSUE_FEE_TYPE = 'IRHPGVISSUE';

    public const IRFO_GV_FEE_TYPE = 'IRFOGVPERMIT';

    public const EMISSIONS_CATEGORY_EURO5 = 'emissions_cat_euro5';

    public const EMISSIONS_CATEGORY_EURO6 = 'emissions_cat_euro6';

    public const EMISSIONS_CATEGORY_NA = 'emissions_cat_na';

    // Question data types
    public const QUESTION_TYPE_STRING = 'question_type_string';

    public const QUESTION_TYPE_INTEGER = 'question_type_integer';

    public const QUESTION_TYPE_BOOLEAN = 'question_type_boolean';

    public const QUESTION_TYPE_DATE = 'question_type_date';

    public const QUESTION_TYPE_CUSTOM = 'question_type_custom';

    // Business process
    public const BUSINESS_PROCESS_APG = 'app_business_process_apg';

    public const BUSINESS_PROCESS_APGG = 'app_business_process_apgg';

    public const BUSINESS_PROCESS_APSG = 'app_business_process_apsg';

    public const COMPLAIN_STATUS_OPEN = 'ecst_open';

    public const COMPLAIN_STATUS_CLOSED = 'ecst_closed';

    public const LICENCE_STATUS_RULE_CURTAILED = 'lsts_curtailed';

    public const LICENCE_STATUS_RULE_REVOKED = 'lsts_revoked';

    public const LICENCE_STATUS_RULE_SUSPENDED = 'lsts_suspended';

    /**
     * PSV types
     */
    public const PSV_TYPE_SMALL  = 'vhl_t_a';

    public const PSV_TYPE_MEDIUM = 'vhl_t_b';

    public const PSV_TYPE_LARGE  = 'vhl_t_c';

    public const TASK_ALLOCATION_TYPE_SIMPLE  = 'task_at_simple';

    public const TASK_ALLOCATION_TYPE_MEDIUM  = 'task_at_medium';

    public const TASK_ALLOCATION_TYPE_COMPLEX = 'task_at_complex';

    public const INSPECTION_REPORT_TYPE_MAINTENANCE_REQUEST = 'insp_rep_t_maint';

    public const INSPECTION_RESULT_TYPE_NEW = 'insp_res_t_new';

    public const APPLICATION_TYPE_NEW = 0;

    public const APPLICATION_TYPE_VARIATION = 1;

    public const INTERIM_STATUS_REQUESTED = 'int_sts_requested';

    public const INTERIM_STATUS_INFORCE = 'int_sts_in_force';

    public const INTERIM_STATUS_REFUSED = 'int_sts_refused';

    public const INTERIM_STATUS_REVOKED = 'int_sts_revoked';

    public const INTERIM_STATUS_GRANTED = 'int_sts_granted';

    public const WITHDRAWN_REASON_WITHDRAWN    = 'withdrawn';

    public const WITHDRAWN_REASON_REG_IN_ERROR = 'reg_in_error';

    // Queue message statuses
    public const QUEUE_STATUS_QUEUED = 'que_sts_queued';

    public const QUEUE_STATUS_PROCESSING = 'que_sts_processing';

    public const QUEUE_STATUS_COMPLETE = 'que_sts_complete';

    public const QUEUE_STATUS_FAILED = 'que_sts_failed';

    public const CONTINUATION_DETAIL_STATUS_PREPARED = 'con_det_sts_prepared';

    public const CONTINUATION_DETAIL_STATUS_PRINTING = 'con_det_sts_printing';

    public const CONTINUATION_DETAIL_STATUS_PRINTED = 'con_det_sts_printed';

    public const CONTINUATION_DETAIL_STATUS_UNACCEPTABLE = 'con_det_sts_unacceptable';

    public const CONTINUATION_DETAIL_STATUS_ACCEPTABLE = 'con_det_sts_acceptable';

    public const CONTINUATION_DETAIL_STATUS_COMPLETE = 'con_det_sts_complete';

    public const CONTINUATION_DETAIL_STATUS_ERROR = 'con_det_sts_error';

    public const APPLICATION_COMPLETION_STATUS_NOT_STARTED = 0;

    public const APPLICATION_COMPLETION_STATUS_INCOMPLETE = 1;

    public const APPLICATION_COMPLETION_STATUS_COMPLETE = 2;

    public const BILATERAL_PERMIT_USAGE = 'bi-permit-usage';

    public const BILATERAL_NUMBER_OF_PERMITS = 'bi-number-of-permits';

    // journey
    public const JOURNEY_SINGLE = 'journey_single';

    public const JOURNEY_MULTIPLE = 'journey_multiple';

    //Application vehicle types
    public const APP_VEHICLE_TYPE_MIXED = 'app_veh_type_mixed';

    public const APP_VEHICLE_TYPE_LGV = 'app_veh_type_lgv';

    public const APP_VEHICLE_TYPE_HGV = 'app_veh_type_hgv';

    public const APP_VEHICLE_TYPE_PSV = 'app_veh_type_psv';
}
