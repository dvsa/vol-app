<?php

namespace Dvsa\Olcs\Api\Entity;

/**
 * IRHP Interface
 */
interface IrhpInterface
{
    public const STATUS_CANCELLED = 'permit_app_cancelled';
    public const STATUS_NOT_YET_SUBMITTED = 'permit_app_nys';
    public const STATUS_UNDER_CONSIDERATION = 'permit_app_uc';
    public const STATUS_WITHDRAWN = 'permit_app_withdrawn';
    public const STATUS_AWAITING_FEE = 'permit_app_awaiting';
    public const STATUS_FEE_PAID = 'permit_app_fee_paid';
    public const STATUS_UNSUCCESSFUL = 'permit_app_unsuccessful';
    public const STATUS_ISSUING = 'permit_app_issuing';
    public const STATUS_VALID = 'permit_app_valid';
    public const STATUS_EXPIRED = 'permit_app_expired';
    public const STATUS_TERMINATED = 'permit_app_terminated';
    public const STATUS_DECLINED = 'permit_app_declined';

    public const ALL_STATUSES = [
        self::STATUS_CANCELLED,
        self::STATUS_NOT_YET_SUBMITTED,
        self::STATUS_UNDER_CONSIDERATION,
        self::STATUS_WITHDRAWN,
        self::STATUS_AWAITING_FEE,
        self::STATUS_FEE_PAID,
        self::STATUS_UNSUCCESSFUL,
        self::STATUS_ISSUING,
        self::STATUS_VALID,
        self::STATUS_EXPIRED,
        self::STATUS_TERMINATED,
        self::STATUS_DECLINED,
    ];

    public const ACTIVE_STATUSES = [
        self::STATUS_NOT_YET_SUBMITTED,
        self::STATUS_UNDER_CONSIDERATION,
        self::STATUS_AWAITING_FEE,
        self::STATUS_FEE_PAID,
        self::STATUS_ISSUING,
    ];

    public const PRE_GRANT_STATUSES = [
        self::STATUS_UNDER_CONSIDERATION
    ];

    public const SOURCE_SELFSERVE = 'app_source_selfserve';
    public const SOURCE_INTERNAL = 'app_source_internal';

    public function getId();
}
