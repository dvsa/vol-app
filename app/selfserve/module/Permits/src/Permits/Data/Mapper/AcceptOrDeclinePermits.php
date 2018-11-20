<?php

namespace Permits\Data\Mapper;

use Common\View\Helper\CurrencyFormatter;
use Common\Service\Helper\TranslationHelperService;

/**
 * Mapper for the FeePartSuccessful / Accept or Decline page
 */
class AcceptOrDeclinePermits
{
    /**
     * Maps data appropriately for the Definition List on the FeePartSuccessful page
     *
     * @param array $data an array of data retrieved from the backend
     * @return array
     */
    public static function mapForDisplay(array $data)
    {
        $currencyFormatter = new CurrencyFormatter;
        $data = ApplicationFees::mapForDisplay($data);

        $data['validityPeriod'] = self::formatValidityPeriod($data);
        $data['issuingFee'] = $data['irhpPermitApplications'][0]['permitsAwarded'] . ' x ' . $currencyFormatter($data['issueFee']);
        $data['issuingFeeTotal'] = $currencyFormatter($data['totalFee']);

        return $data;
    }

    /**
     * formats the validity period for display
     *
     * @param array a collection of application data as returned from backend
     * @return string the validity period formatted for display
     */
    private static function formatValidityPeriod($data)
    {
        $stock = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        $fromDate = date(\DATE_FORMAT, strtotime($stock['validFrom']));
        $toDate = date(\DATE_FORMAT, strtotime($stock['validTo']));

        return $fromDate . ' to ' . $toDate; //@todo: need to translate the word 'to'
    }
}
