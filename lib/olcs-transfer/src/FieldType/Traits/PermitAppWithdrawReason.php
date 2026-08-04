<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Reason for permit app being withdrawn
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait PermitAppWithdrawReason
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "permits_app_withdraw_by_user",
     *              "permits_app_withdraw_declined",
     *              "permits_app_withdraw_not_paid",
     *              "permits_app_withdraw_notsuccess",
     *              "permits_app_withdraw_permits_rev"
     *          }
     *      }
     * )
     */
    protected $reason;

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
