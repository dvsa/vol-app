<?php

namespace Permits\Data\Mapper;

use JsonSchema\Exception\ResourceNotFoundException;

/**
 *
 * ECMT Constrained Countries mapper
 */

class ValidEcmtPermitConstrainedCountries
{
    public static function mapForDisplay(array $data)
    {
        if (empty($data)) {
            throw new ResourceNotFoundException('Permits not found');
        }
        $newResults = [];
        foreach ($data['validPermits']['results'] as $datum) {
            $constrainedCountries = [];

            if (!empty($datum['countries'])) {
                $allCountries = $data['ecmtConstrainedCountries']['results'];
                foreach ($allCountries as $key => $row) {
                    foreach ($datum['countries'] as $key2 => $row2) {
                        if ($row['id'] == $row2['id']) {
                            unset($allCountries[$key]);
                            break;
                        }
                    }
                }
                $constrainedCountries = $allCountries;
            }
            $newResults[] = [
                'permitNumber' => $datum['permitNumber'],
                'countries' => $constrainedCountries
            ];
        }

        unset ($data['ecmtConstrainedCountries']);

        $data['validPermits']['results'] = $newResults;

        return $data['validPermits'];
    }
}
