<?php

namespace Permits\Data\Mapper;

use Permits\Controller\Config\DataSource\EcmtConstrainedCountriesList as EcmtConstrainedCountriesListDataSource;
use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\UnpaidEcmtPermits as UnpaidEcmtPermitsDataSource;

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

        $key = isset($data[UnpaidEcmtPermitsDataSource::DATA_KEY])
            ? UnpaidEcmtPermitsDataSource::DATA_KEY : ValidEcmtPermitsDataSource::DATA_KEY;

        $allCountries = $data[EcmtConstrainedCountriesListDataSource::DATA_KEY]['results'];
        $allCountryIds = array_column($allCountries, 'id');

        $newResults = [];

        foreach ($data[$key]['results'] as $permit) {
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
                'emissionsCategory' => $permit['emissionsCategory'],
                'countries' => $constrainedCountries,
                'irhpPermitApplication' => $permit['irhpPermitApplication'] ?? null,
                'startDate' => $permit['startDate'] ?? null,
                'expiryDate' => $permit['expiryDate'] ?? null,
            ];
        }

        unset($data[EcmtConstrainedCountriesListDataSource::DATA_KEY]);

        $data[$key]['results'] = $newResults;

        return $data[$key];
    }
}
