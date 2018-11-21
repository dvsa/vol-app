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

        $stock = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        $data['validityPeriod']['fromDate'] = $stock['validFrom'];
        $data['validityPeriod']['toDate'] = $stock['validTo'];
        $data['numPermitsAwarded'] = $data['irhpPermitApplications'][0]['permitsAwarded'];
        $data['issuingFee'] = $data['issueFee'];
        $data['issuingFeeTotal'] = $data['totalFee'];

        return $data;
    }
}
