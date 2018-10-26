<?php

namespace Permits\Data\Mapper;

/**
 * ECMT Constrained Countries mapper
 */
class ValidEcmtPermitConstrainedCountries
{
    /**
     * Merges valid permit data with the constrained countries data
     *
     * @param array $data input data
     *
     * @return array
     */
    public static function mapForDisplay(array $data): array
    {
        if (empty($data)) {
            return $data;
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

        unset($data['ecmtConstrainedCountries']);
        $data['validPermits']['results'] = $newResults;

        return $data['validPermits'];
    }
}
