<?php

namespace Permits\Data\Mapper;

use JsonSchema\Exception\ResourceNotFoundException;

/**
 *
 * Valid ECMT permits list mapper
 */
class ValidEcmtPermits
{
    public static function mapForDisplay(array $data)
    {
        $permits = [];

        if (empty($data)) {
            throw new ResourceNotFoundException('Permits not found');
        }


        foreach ($data['result'] as $permit) {
            $permits[] = [
                'permitNumber' => $permit['permitNumber'],
                'countries' => $permit['irhpPermitRange']['countrys']
            ];
        }

        $firstPermit = $data['result'][0];
        return [
            'irhpPermitStock' => $firstPermit['irhpPermitRange']['irhpPermitStock'],
            'results' => $permits,
            'count' => $data['count'],
            'ref' => $firstPermit['irhpPermitApplication']['ecmtPermitApplication']['applicationRef'],
            'status' => $firstPermit['status']['description']
        ];
    }
}
