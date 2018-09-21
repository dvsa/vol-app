<?php

namespace Permits\Data\Mapper;

/**
 *
 * Valid ECMT permits list mapper
 */
class ValidEcmtPermits
{
    public static function mapForDisplay (array $data) {
        $permits = [];

        foreach ($data['result'] as $permit) {
            $rc = [];
            if (count($permit['irhpPermitRange']['countrys']) > 0) {
                foreach ($permit['irhpPermitRange']['countrys'] as $restrictedCountry) {
                    $rc[] = $restrictedCountry['countryDesc'];
                }
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
