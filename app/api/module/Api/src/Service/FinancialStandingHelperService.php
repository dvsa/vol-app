<?php

namespace Dvsa\Olcs\Api\Service;

use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Domain\Repository\Organisation;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Financial Standing Helper Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingHelperService implements FactoryInterface
{
    /**
     * @var array $rates
     */
    protected $rates;

    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate
     */
    protected $ratesRepo;

    /**
     * @var Organisation
     */
    protected $organisationRepo;

    /**
     * @var Application
     */
    protected $applicationRepo;

    /**
     * Get the amount of required finance for an organisation
     *
     * @param int $organisationId Organisation ID
     *
     * @return int Amount in pounds
     */
    public function getFinanceCalculationForOrganisation($organisationId)
    {
        $auths = [];
        /** @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation */
        $organisation = $this->organisationRepo->fetchById($organisationId);

        $applications = $this->applicationRepo->fetchActiveForOrganisation($organisation->getId());
        if (!empty($applications)) {
            foreach ($applications as $app) {
                // filter new apps only
                if ($app->isVariation()) {
                    continue;
                }
                $auths[] = [
                    'type' => $app->getLicenceType()->getId(),
                    'count' => $app->getTotAuthVehicles(),
                    'hgvCount' => $app->getTotAuthHgvVehiclesZeroCoalesced(),
                    'lgvCount' => $app->getTotAuthLgvVehiclesZeroCoalesced(),
                    'category' => $app->getGoodsOrPsv()->getId(),
                ];
            }
        }

        $licences = $organisation->getActiveLicences();
        if (!empty($licences)) {
            foreach ($licences as $licence) {
                $auths[] = [
                    'type' => $licence->getLicenceType()->getId(),
                    'count' => $licence->getTotAuthVehicles(),
                    'hgvCount' => $licence->getTotAuthHgvVehiclesZeroCoalesced(),
                    'lgvCount' => $licence->getTotAuthLgvVehiclesZeroCoalesced(),
                    'category' => $licence->getGoodsOrPsv()->getId(),
                ];
            }
        }

        return $this->getFinanceCalculation($auths);
    }

    /**
     * Takes an array of vehicle authorisations (example below) and
     * returns the required finance amount
     *
     * array (
     *   0 => array (
     *     'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
     *     'type' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
     *     'count' => 4,
     *     'hgvCount' => 3,
     *     'lgvCount' => 1,
     *   ),
     *   1 => array (
     *     'category' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
     *     'type' => Licence::LICENCE_TYPE_RESTRICTED,
     *     'count' => 3,
     *     'hgvCount' => 3,
     *     'lgvCount' => 0,
     *   ),
     *   2 => array (
     *     'category' => Licence::LICENCE_CATEGORY_PSV,
     *     'type' => Licence::LICENCE_TYPE_RESTRICTED,
     *     'count' => 1,
     *     'hgvCount' => 1,
     *     'lgvCount' => 0,
     *   ),
     * )
     *
     * Calculation:
     *    1 x 7000  first vehicle rate GV/SI/HGV (7000)
     * +  3 x 3900  additional vehicle rate GV/SI/HGV (11700)
     * +  1 x 800   additional vehicle rate GV/SI/LGV (800)
     * +  3 x 1700  additional vehicle rate GV/R (5100)
     * +  1 x 2700  additional vehicle rate PSV/R (2700)
     * -----------
     *       27300
     *
     * @return int
     */
    public function getFinanceCalculation(array $auths)
    {
        // 1. Sort the array so the correct (higher) 'first vehicle' charge is
        // applied (i.e. ensure any PSV apps/licences are handled first)
        usort(
            $auths,
            function ($a, $b) {
                unset($b); // not used in comparison
                return $a['category'] === Licence::LICENCE_CATEGORY_PSV ? -1 : 1;
            }
        );

        // 2. Get first vehicle charge and whether to decrement hgvCount or lgvCount
        $firstVehicleKey = null;
        $firstVehicleCountType = null;
        $firstVehicleMetadata = $this->getFirstVehicleMetadata($auths);

        if (!is_null($firstVehicleMetadata)) {
            $firstVehicleKey = $firstVehicleMetadata['key'];
            $firstVehicleCountType = $firstVehicleMetadata['countType'];
        }

        // 3. Ensure we don't double-count the first vehicle
        $firstVehicleCharge = 0;
        if ($firstVehicleKey !== null) {
            $auths[$firstVehicleKey]['count']--;
            $auths[$firstVehicleKey][$firstVehicleCountType]--;

            $auth = $auths[$firstVehicleKey];
            $rateVehicleType = FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE;
            if ($this->useSeparateHgvAndLgvRates($auth)) {
                if ($firstVehicleCountType == 'hgvCount') {
                    $rateVehicleType = FinancialStandingRate::VEHICLE_TYPE_HGV;
                } else {
                    $rateVehicleType = FinancialStandingRate::VEHICLE_TYPE_LGV;
                }
            }

            $firstVehicleCharge = $this->getFirstVehicleRate(
                $auth['type'],
                $auth['category'],
                $rateVehicleType
            );
        }

        // 4. Get the additional vehicle charges
        $additionalVehicleCharge = 0;
        foreach ($auths as $auth) {
            if ($this->useSeparateHgvAndLgvRates($auth)) {
                $rate = $this->getAdditionalVehicleRate(
                    $auth['type'],
                    $auth['category'],
                    FinancialStandingRate::VEHICLE_TYPE_HGV
                );
                $additionalVehicleCharge += ($auth['hgvCount'] * $rate);
                $rate = $this->getAdditionalVehicleRate(
                    $auth['type'],
                    $auth['category'],
                    FinancialStandingRate::VEHICLE_TYPE_LGV
                );
                $additionalVehicleCharge += ($auth['lgvCount'] * $rate);
            } else {
                $rate = $this->getAdditionalVehicleRate(
                    $auth['type'],
                    $auth['category'],
                    FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
                );
                $additionalVehicleCharge += ($auth['count'] * $rate);
            }
        }

        // 5. Return the total required finance
        return $firstVehicleCharge + $additionalVehicleCharge;
    }

    /**
     * Return metadata regarding the first vehicle given an array of auths
     *
     *
     * @return array
     */
    private function getFirstVehicleMetadata(array $auths)
    {
        $higherChargeTypes = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
        ];

        $goodsOrPsvCategories = [
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_CATEGORY_PSV,
        ];

        $latestRestrictedKey = null;
        $latestLgvKey = null;

        $filteredAuths = [];
        foreach ($auths as $key => $auth) {
            if ($auth['count'] > 0) {
                $filteredAuths[$key] = $auth;
            }
        }

        foreach ($filteredAuths as $key => $auth) {
            if (in_array($auth['type'], $higherChargeTypes) && in_array($auth['category'], $goodsOrPsvCategories)) {
                if ($this->useSeparateHgvAndLgvRates($auth)) {
                    if ($auth['hgvCount'] > 0) {
                        return $this->generateFirstVehicleMetadataResponse($key, 'hgvCount');
                    } else {
                        $latestLgvKey = $key;
                    }
                } else {
                    return $this->generateFirstVehicleMetadataResponse($key, 'hgvCount');
                }
            } elseif ($auth['type'] == Licence::LICENCE_TYPE_RESTRICTED) {
                $latestRestrictedKey = $key;
            }
        }

        if (!is_null($latestRestrictedKey)) {
            return $this->generateFirstVehicleMetadataResponse($latestRestrictedKey, 'hgvCount');
        }

        if (!is_null($latestLgvKey)) {
            return $this->generateFirstVehicleMetadataResponse($latestLgvKey, 'lgvCount');
        }

        return null;
    }

    /**
     * Generate and return a response for the getFirstVehicleMetadata method
     *
     * @param string $key
     * @param string $countType
     *
     * @return array
     */
    private function generateFirstVehicleMetadataResponse($key, $countType)
    {
        return [
            'key' => $key,
            'countType' => $countType,
        ];
    }

    /**
     * Should this auth be associated with rates with a vehicle type of HGV/LGV rather than a vehicle type of
     * "not applicable"?
     *
     *
     * @return float
     */
    private function useSeparateHgvAndLgvRates(array $auth)
    {
        return $auth['category'] == Licence::LICENCE_CATEGORY_GOODS_VEHICLE &&
            $auth['type'] == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL;
    }

    /**
     * @param string $licenceType
     * @param string $goodsOrPsv
     * @param string $vehicleType
     *
     * @return float
     */
    public function getFirstVehicleRate($licenceType, $goodsOrPsv, $vehicleType)
    {
        foreach ($this->getRates() as $rate) {
            if (
                $rate->getGoodsOrPsv()->getId() == $goodsOrPsv &&
                $rate->getLicenceType()->getId() == $licenceType &&
                $rate->getVehicleType()->getId() == $vehicleType
            ) {
                return (float) $rate->getFirstVehicleRate();
            }
        }

        return null;
    }

    /**
     * @param string $licenceType
     * @param string $goodsOrPsv
     * @param string $vehicleType
     *
     * @return float
     */
    public function getAdditionalVehicleRate($licenceType, $goodsOrPsv, $vehicleType)
    {
        foreach ($this->getRates() as $rate) {
            if (
                $rate->getGoodsOrPsv()->getId() == $goodsOrPsv &&
                $rate->getLicenceType()->getId() == $licenceType &&
                $rate->getVehicleType()->getId() == $vehicleType
            ) {
                return (float) $rate->getAdditionalVehicleRate();
            }
        }

        return null;
    }

    /**
     * Get rates in effect
     * @return array FinancialStandingRate[]
     */
    protected function getRates()
    {
        // we only make one call to look up standing rates
        if (is_null($this->rates)) {
            $date = new \DateTime();
            $this->rates = $this->ratesRepo->fetchRatesInEffect($date);
        }
        return $this->rates;
    }

    /**
     * Gets the vehicle rates to display in the help section of the page.
     *
     * @return array
     */
    public function getRatesForView()
    {
        return [
            'restrictedHeavyGoodsFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'restrictedHeavyGoodsAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'restrictedPassengerServiceFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_CATEGORY_PSV,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'restrictedPassengerServiceAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_RESTRICTED,
                Licence::LICENCE_CATEGORY_PSV,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'standardNationalHeavyGoodsFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'standardNationalHeavyGoodsAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'standardNationalPassengerServiceFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_CATEGORY_PSV,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'standardNationalPassengerServiceAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_CATEGORY_PSV,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'standardInternationalHeavyGoodsFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_HGV
            ),
            'standardInternationalHeavyGoodsAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_HGV
            ),
            'standardInternationalLightGoodsFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_LGV
            ),
            'standardInternationalLightGoodsAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                FinancialStandingRate::VEHICLE_TYPE_LGV
            ),
            'standardInternationalPassengerServiceFirst' => $this->getFirstVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_CATEGORY_PSV,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
            'standardInternationalPassengerServiceAdditional' => $this->getAdditionalVehicleRate(
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                Licence::LICENCE_CATEGORY_PSV,
                FinancialStandingRate::VEHICLE_TYPE_NOT_APPLICABLE
            ),
        ];
    }

    /**
     * Get the amount of required finance across all applications and licences within the associated organisation. If
     * $includeApplicationInCalculation is true, the provided application will be factored into the result returned,
     * otherwise it will not
     *
     * @param ApplicationEntity $application
     * @param bool $includeApplicationInCalculation Whether to include the provided application in the calculation
     *
     * @return int Amount in pounds
     */
    public function getRequiredFinance($application, $includeApplicationInCalculation = true)
    {
        $auths = [];

        if ($includeApplicationInCalculation) {
            $type = null;
            if ($application->getLicenceType()) {
                $type = $application->getLicenceType()->getId();
            }
            $auths[] = [
                'type' => $type,
                'count' => $application->getTotAuthVehicles(),
                'hgvCount' => $application->getTotAuthHgvVehiclesZeroCoalesced(),
                'lgvCount' => $application->getTotAuthLgvVehiclesZeroCoalesced(),
                'category' => $application->getGoodsOrPsv()->getId(),
            ];

            $licences = $application->getOtherActiveLicencesForOrganisation();
        } else {
            $licences = $application->getActiveLicencesForOrganisation();
        }

        // add the counts for each licence
        foreach ($licences as $licence) {
            $auths[] = [
                'type' => $licence->getLicenceType()->getId(),
                'count' => $licence->getTotAuthVehicles(),
                'hgvCount' => $licence->getTotAuthHgvVehiclesZeroCoalesced(),
                'lgvCount' => $licence->getTotAuthLgvVehiclesZeroCoalesced(),
                'category' => $licence->getGoodsOrPsv()->getId(),
            ];
        }

        // add the counts for each other application
        $applications = $this->getOtherNewApplications($application);
        foreach ($applications as $app) {
            if (!is_null($app->getGoodsOrPsv())) {
                $type = null;
                if ($app->getLicenceType()) {
                    $type = $app->getLicenceType()->getId();
                }
                $auths[] = [
                    'type' => $type,
                    'count' => $app->getTotAuthVehicles(),
                    'hgvCount' => $app->getTotAuthHgvVehiclesZeroCoalesced(),
                    'lgvCount' => $app->getTotAuthLgvVehiclesZeroCoalesced(),
                    'category' => $app->getGoodsOrPsv()->getId(),
                ];
            }
        }

        return $this->getFinanceCalculation($auths);
    }

    /**
     * Get all active applications belonging to the organisation associated with the provided application, with the
     * exception of the provided application and any applications that are variations
     *
     *
     * @return array
     */
    public function getOtherNewApplications(ApplicationEntity $application)
    {
        $organisation = $application->getLicence()->getOrganisation();

        $applications = $this->applicationRepo->fetchActiveForOrganisation($organisation->getId());

        return array_filter(
            $applications,
            function ($app) use ($application) {
                if ($app->isVariation()) {
                    // exclude variations
                    return false;
                }
                // exclude the current application so we don't double-count
                return $app->getId() !== $application->getId();
            }
        );
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->ratesRepo = $container->get('RepositoryServiceManager')->get('FinancialStandingRate');
        $this->organisationRepo = $container->get('RepositoryServiceManager')->get('Organisation');
        $this->applicationRepo = $container->get('RepositoryServiceManager')->get('Application');
        return $this;
    }
}
