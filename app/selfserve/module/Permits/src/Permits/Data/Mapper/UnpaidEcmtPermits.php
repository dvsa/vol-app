<?php

namespace Permits\Data\Mapper;

/**
 *
 * Unpaid ECMT permits list mapper
 */
class UnpaidEcmtPermits
{
    public function mapForDisplay(array $data)
    {
        $permits = [];

        foreach ($data['result'] as $permit) {
            $permits[] = [
                'permitNumber' => $permit['permitNumber'],
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
