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
            $rc = [];
            foreach ($permit['irhpPermitRange']['countrys'] as $restrictedCountry) {
                $rc[] = $restrictedCountry['countryDesc'];
            }
            $permits[] = [
                'permitNumber' => $permit['permitNumber'],
                'countries' => implode(', ', $rc)
            ];
            $permitStock = $permit['irhpPermitRange']['irhpPermitStock'];
            $ref = $permit['irhpPermitApplication']['ecmtPermitApplication']['applicationRef'];
            $status = $permit['status']['description'];
        }

        return [
            'irhpPermitStock' => $permitStock,
            'results' => $permits,
            'count' => $data['count'],
            'ref' => $ref,
            'status' => $status
        ];
    }
}
