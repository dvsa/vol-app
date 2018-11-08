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

        $allCountries = $data['ecmtConstrainedCountries']['results'];
        $allCountryIds = array_column($allCountries, 'id');

        $newResults = [];

        foreach ($data['validPermits']['results'] as $permitKey => $permit) {
            $includedCountryIds = array_column($permit['countries'], 'id');
            $excludedCountryIds = array_diff($allCountryIds, $includedCountryIds);

            $constrainedCountries = [];
            foreach ($allCountries as $country) {
                if (in_array($country['id'], $excludedCountryIds)) {
                    $constrainedCountries[] = $country;
                }
            }
            $newResults[] = [
                'permitNumber' => $permit['permitNumber'],
                'countries' => $constrainedCountries
            ];
        }

        unset($data['ecmtConstrainedCountries']);
        $data['validPermits']['results'] = $newResults;

        return $data['validPermits'];
    }
}
