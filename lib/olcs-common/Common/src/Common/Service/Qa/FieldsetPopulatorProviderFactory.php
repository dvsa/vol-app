<?php

namespace Common\Service\Qa;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FieldsetPopulatorProviderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FieldsetPopulatorProvider
    {
        $fieldsetPopulatorProvider = new FieldsetPopulatorProvider();

        $populators = [
            'checkbox' => 'QaCheckboxFieldsetPopulator',
            'text' => 'QaTextFieldsetPopulator',
            'radio' => 'QaRadioFieldsetPopulator',
            'ecmt_no_of_permits_either' => 'QaEcmtNoOfPermitsEitherStrategySelectingFieldsetPopulator',
            'ecmt_no_of_permits_both' => 'QaEcmtNoOfPermitsBothStrategySelectingFieldsetPopulator',
            'ecmt_check_ecmt_needed' => 'QaEcmtCheckEcmtNeededFieldsetPopulator',
            'ecmt_st_permit_usage' => 'QaEcmtPermitUsageFieldsetPopulator',
            'ecmt_st_restricted_countries' => 'QaEcmtRestrictedCountriesFieldsetPopulator',
            'ecmt_st_annual_trips_abroad' => 'QaEcmtAnnualTripsAbroadFieldsetPopulator',
            'ecmt_st_international_journeys' => 'QaEcmtInternationalJourneysFieldsetPopulator',
            'ecmt_st_earliest_permit_date' => 'QaEcmtShortTermEarliestPermitDateFieldsetPopulator',
            'ecmt_rem_permit_start_date' => 'QaEcmtRemovalPermitStartDateFieldsetPopulator',
            'cert_road_mot_expiry_date' => 'QaCertRoadworthinessMotExpiryDateFieldsetPopulator',
            'bilateral_permit_usage' => 'QaBilateralPermitUsageFieldsetPopulator',
            'bilateral_cabotage_only' => 'QaBilateralCabotageOnlyFieldsetPopulator',
            'bilateral_standard_and_cabotage' => 'QaBilateralStandardAndCabotageFieldsetPopulator',
            'bilateral_number_of_permits' => 'QaBilateralNoOfPermitsFieldsetPopulator',
            'bilateral_number_of_permits_morocco' => 'QaBilateralNoOfPermitsMoroccoFieldsetPopulator',
            'bilateral_third_country' => 'QaBilateralThirdCountryFieldsetPopulator',
            'bilateral_emissions_standards' => 'QaBilateralEmissionsStandardsFieldsetPopulator',
            'ecmt_sectors' => 'QaEcmtSectorsFieldsetPopulator',
        ];

        foreach ($populators as $type => $serviceName) {
            $fieldsetPopulatorProvider->registerPopulator(
                $type,
                $container->get($serviceName)
            );
        }

        return $fieldsetPopulatorProvider;
    }
}
