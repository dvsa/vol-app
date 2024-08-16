<?php

namespace Dvsa\Olcs\Api\Entity\Fee;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * FeeType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="fee_type",
 *    indexes={
 *        @ORM\Index(name="ix_fee_type_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_fee_type_licence_type", columns={"licence_type"}),
 *        @ORM\Index(name="ix_fee_type_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_fee_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_fee_type_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_fee_type_accrual_rule", columns={"accrual_rule"}),
 *        @ORM\Index(name="ix_fee_type_fee_type", columns={"fee_type"}),
 *        @ORM\Index(name="ix_fee_type_is_miscellaneous", columns={"is_miscellaneous"})
 *    }
 * )
 */
class FeeType extends AbstractFeeType
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
    public const FEE_TYPE_ADJUSTMENT = 'ADJUSTMENT';

    public const FEE_TYPE_IRHP_APP = 'IRHPGVAPP';
    public const FEE_TYPE_IRHP_ISSUE = 'IRHPGVISSUE';

    public const FEE_TYPE_ECMT_APP = 'IRHPGVAPP';
    public const FEE_TYPE_ECMT_ISSUE = 'IRHPGVISSUE';

    public const FEE_TYPE_ECMT_APP_PRODUCT_REF = 'IRHP_GV_APP_ECMT';
    public const FEE_TYPE_ECMT_REMOVAL_ISSUE_PRODUCT_REF = 'IRFO_GV_ECMT_COM_RM_PERM_FEE';
    public const FEE_TYPE_ECMT_SHORT_TERM_ISSUE_PRODUCT_REF = 'IRHP_GV_ECMT_PERMIT_MONTHLY';
    public const FEE_TYPE_IRHP_APP_BILATERAL_PRODUCT_REF = 'IRHP_GV_APP_BILATERAL_ANN';
    public const FEE_TYPE_IRHP_APP_BILATERAL_SINGLE_PRODUCT_REF = 'IRHP_GV_APP_BILATERAL_SINGLE';
    public const FEE_TYPE_IRHP_APP_MULTILATERAL_PRODUCT_REF = 'IRHP_GV_APP_MULTILAT_ANN';
    public const FEE_TYPE_IRHP_ISSUE_BILATERAL_PRODUCT_REF = 'IRHP_GV_PERMIT_BILATERAL_ANN';

    public const FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF = 'IRHP_GV_PERMIT_BILATERAL_SINGL';
    public const FEE_TYPE_IRHP_ISSUE_MULTILATERAL_PRODUCT_REF = 'IRHP_GV_PERMIT_MULTILAT_ANN';
    public const FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU = 'IRFO_GV_SINGLE_J_PERMIT_FEE';
    public const FEE_TYPE_IRHP_ISSUE_BILATERAL_MULTI_MOROCCO_PRODUCT_REF = 'IRFO_GV_MOROCC_15J_PERMIT_FEE';

    public const COST_CENTRE_REF_TYPE_LICENSING = 'TA';
    public const COST_CENTRE_REF_TYPE_IRFO = 'IR';

    // country codes for CPMS
    public const COUNTRY_CODE_GB = 'GB';
    public const COUNTRY_CODE_NI = 'NI';

    public const FEE_TYPE_ECMT_ISSUE_100_PRODUCT_REF = 'IRHP_GV_ECMT_100_PERMIT_FEE';
    public const FEE_TYPE_ECMT_ISSUE_75_PRODUCT_REF = 'IRHP_GV_ECMT_75_PERMIT_FEE';
    public const FEE_TYPE_ECMT_ISSUE_50_PRODUCT_REF = 'IRHP_GV_ECMT_50_PERMIT_FEE';
    public const FEE_TYPE_ECMT_ISSUE_25_PRODUCT_REF = 'IRHP_GV_ECMT_25_PERMIT_FEE';

    public const FEE_TYPE_IRHP_MULTI_ISSUE_75_PRODUCT_REF = 'IRHP_GV_MULTI_75_PERMIT_FEE';
    public const FEE_TYPE_IRHP_MULTI_ISSUE_50_PRODUCT_REF = 'IRHP_GV_MULTI_50_PERMIT_FEE';
    public const FEE_TYPE_IRHP_MULTI_ISSUE_25_PRODUCT_REF = 'IRHP_GV_MULTI_25_PERMIT_FEE';

    /**
     * Alias of getIsMiscellaneous
     * @return bool
     */
    public function isMiscellaneous()
    {
        return (bool) $this->getIsMiscellaneous();
    }

    public function isAdjustment()
    {
        return $this->getFeeType()->getId() === self::FEE_TYPE_ADJUSTMENT;
    }

    public function getCalculatedBundleValues()
    {
        return [
            'displayValue' => $this->getAmount(),
        ];
    }

    /**
     * AC from OLCS-10611
     * @return string amount
     */
    public function getAmount()
    {
        return $this->getFixedValue() > 0 ? $this->getFixedValue() : $this->getFiveYearValue();
    }

    /**
     * Get the CPMS country code based on the is_ni flag
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->getIsNi() === 'Y' ? self::COUNTRY_CODE_NI : self::COUNTRY_CODE_GB;
    }

    /**
     * Is show quantity
     *
     * @return bool
     */
    public function isShowQuantity(): bool
    {
        return in_array(
            $this->getFeeType()->getId(),
            [
                self::FEE_TYPE_IRFOPSVCOPY,
                self::FEE_TYPE_IRFOPSVANN,
                self::FEE_TYPE_IRFOGVPERMIT
            ]
        );
    }

    /**
     * @return bool
     */
    public function isEcmtApplication(): bool
    {
        return $this->feeType->getId() === self::FEE_TYPE_ECMT_APP;
    }

    /**
     * @return bool
     */
    public function isEcmtIssue(): bool
    {
        return $this->feeType->getId() === self::FEE_TYPE_ECMT_ISSUE;
    }

    /**
     * @return bool
     */
    public function isInterimGrantFee(): bool
    {
        return $this->getFeeType()->getId() === self::FEE_TYPE_GRANTINT;
    }

    /**
     * @return bool
     */
    public function isIrhpApplicationIssue(): bool
    {
        return $this->feeType->getId() === self::FEE_TYPE_IRHP_ISSUE;
    }

    /**
     * @return bool
     */
    public function isIrhpApplication(): bool
    {
        return in_array($this->feeType->getId(), [self::FEE_TYPE_IRHP_ISSUE, self::FEE_TYPE_IRHP_APP, self::FEE_TYPE_IRFOGVPERMIT]);
    }

    /**
     *
     * @return FeeType
     */
    public function updateNewFeeType(string $effectiveFrom, int $fixedValue, int $annualValue, int $fiveYearValue, FeeType $existingFeeType)
    {
        // Clone existing object and null meta-data so Doctrine sets correct values on save
        $newFeeType = clone $existingFeeType;
        $newFeeType->setId(null);
        $newFeeType->setVersion(1);
        $newFeeType->setLastModifiedBy(null);
        $newFeeType->setLastModifiedOn(null);
        $newFeeType->setCreatedBy(null);
        $newFeeType->setCreatedOn(null);

        // Set values specified on Admin form
        $newFeeType->effectiveFrom = new DateTime($effectiveFrom);
        $newFeeType->fixedValue = $fixedValue;
        $newFeeType->annualValue = $annualValue;
        $newFeeType->fiveYearValue = $fiveYearValue;

        return $newFeeType;
    }
}
