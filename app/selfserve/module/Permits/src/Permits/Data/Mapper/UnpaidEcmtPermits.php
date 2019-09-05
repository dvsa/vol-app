<?php

namespace Permits\Data\Mapper;

/**
 *
 * Unpaid ECMT permits list mapper
 */
class UnpaidEcmtPermits
{
    public static function mapForDisplay(array $data)
    {
        $permits = [];

        $cnt = 0;
        foreach ($data['result'] as $permit) {
            $cnt++;
            $permits[] = [
                'permitNumber' => $cnt,
                'emissionsCategory' => $permit['irhpPermitRange']['emissionsCategory'],
                'countries' => $permit['irhpPermitRange']['countrys'],
            ];
        }

        return [
            'results' => $permits,
            'count' => $data['count'],
        ];
    }
}
