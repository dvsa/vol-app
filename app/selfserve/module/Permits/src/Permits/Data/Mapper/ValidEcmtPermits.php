<?php

namespace Permits\Data\Mapper;

/**
 *
 * Valid ECMT permits list mapper
 */
class ValidEcmtPermits
{
    public function mapForDisplay(array $data)
    {
        $permits = [];

        foreach ($data['results'] as $permit) {
            $permits[] = [
                'permitNumber' => $permit['permitNumber'],
                'emissionsCategory' => $permit['irhpPermitRange']['emissionsCategory'],
                'countries' => $permit['irhpPermitRange']['countrys'],
                'irhpPermitApplication' => $permit['irhpPermitApplication'],
                'startDate' => $permit['startDate'],
                'expiryDate' => $permit['irhpPermitRange']['irhpPermitStock']['validTo'],
            ];
        }
        return [
            'results' => $permits,
            'count' => $data['count'],
        ];
    }
}
