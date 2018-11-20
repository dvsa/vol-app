<?php

namespace Permits\Data\Mapper;

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
    public static function mapForDisplay(array $data): array
    {
        $data = ApplicationFees::mapForDisplay($data);

        $data['validityPeriod'] = self::formatValidityPeriod($data);
        $data['numPermitsAwarded'] = $data['irhpPermitApplications'][0]['permitsAwarded'];
        $data['issuingFee'] = $data['issueFee'];
        $data['issuingFeeTotal'] = $data['totalFee'];

        return $data;
    }

    /**
     * formats the validity period for display
     *
     * @param array a collection of application data as returned from backend
     * @return array the validity period formatted for display
     */
    private static function formatValidityPeriod(array $data): array
    {
        $stock = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        $validityPeriod['fromDate'] = date(\DATE_FORMAT, strtotime($stock['validFrom']));
        $validityPeriod['toDate'] = date(\DATE_FORMAT, strtotime($stock['validTo']));

        return $validityPeriod;
    }
}
