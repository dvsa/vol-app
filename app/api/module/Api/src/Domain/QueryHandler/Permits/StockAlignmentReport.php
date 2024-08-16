<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Permits\StockAlignmentReport as StockAlignmentReportQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Stock alignment report
 */
class StockAlignmentReport extends AbstractQueryHandler
{
    public const WITHOUT_RESTRICTED_COUNTRIES = 'without restricted countries';

    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitStock', 'Country'];

    /** @var array */
    private $remainingStock = [];

    /** @var array */
    private $alignedStock = [];

    /**
     * Handle query
     *
     * @param QueryInterface|StockAlignmentReportQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $stockId = $query->getId();

        // get count of permits for the stock
        $this->remainingStock = $this->getAvailablePermits($stockId);

        // get country entity for Hungary
        $hungary = $this->getRepo('Country')->fetchById(Country::ID_HUNGARY);

        // get all successful, in scope candidate permits in score order
        $candidatePermits = $this->getRepo('IrhpApplication')->getSuccessfulScoreOrderedInScope($stockId);

        foreach ($candidatePermits as $candidatePermit) {
            $emissionsCategory = $candidatePermit->getRequestedEmissionsCategory();
            $restrictedCountries = $candidatePermit->getIrhpPermitApplication()->getIrhpApplication()->getCountrys();

            if (
                !$restrictedCountries->exists(
                    fn($key, $element) => $hungary->getId() === $element->getId()
                )
            ) {
                // all permits allow travel to Hungary so record the candidate as requiring a permit for Hungary
                // regardless whether Hungary was requested in the application
                $restrictedCountries->add($hungary);
            }

            $this->calculateAlignedStock($emissionsCategory, $restrictedCountries);
        }

        return ['rows' => $this->flattenAlignedStock()];
    }

    /**
     * Get available permits as an array of $availablePermits[$emissionCategoryId][$countryId]
     *
     * @param int $stockId
     *
     * @return array
     */
    private function getAvailablePermits($stockId)
    {
        $permitsAvailable = [
            RefData::EMISSIONS_CATEGORY_EURO5_REF => ['total' => 0],
            RefData::EMISSIONS_CATEGORY_EURO6_REF => ['total' => 0],
        ];

        $stock = $this->getRepo('IrhpPermitStock')->fetchById($stockId);

        foreach ($stock->getIrhpPermitRanges() as $range) {
            if ($range->getSsReserve() || $range->getLostReplacement()) {
                // ignore SsReserve or LostReplacement ranges
                continue;
            }

            $emissionCategoryId = $range->getEmissionsCategory()->getId();

            $numberOfPermits = $range->getSize();
            $permitsAvailable[$emissionCategoryId]['total'] += $numberOfPermits;

            foreach ($range->getCountrys() as $country) {
                $countryId = $country->getId();

                if (!isset($permitsAvailable[$emissionCategoryId][$countryId])) {
                    $permitsAvailable[$emissionCategoryId][$countryId] = 0;
                }

                $permitsAvailable[$emissionCategoryId][$countryId] += $numberOfPermits;
            }
        }

        return $permitsAvailable;
    }

    /**
     * Calculate aligned stock
     *
     * @param RefData $emissionsCategory Requested emissions category
     * @param Collection $restrictedCountries Requested restricted countries
     *
     * @return void
     */
    private function calculateAlignedStock(RefData $emissionsCategory, Collection $restrictedCountries)
    {
        $emissionsCategoryId = $emissionsCategory->getId();

        if (
            empty($this->remainingStock[$emissionsCategoryId]['total'])
            && $emissionsCategoryId == RefData::EMISSIONS_CATEGORY_EURO6_REF
        ) {
            // if there are no permits available for that Emissions Standard and the requested Emissions Standard is Euro6
            // then reduce the total number of Euro5 permits by 1 and increase the total number of Euro6 permits by 1
            $this->remainingStock[RefData::EMISSIONS_CATEGORY_EURO5_REF]['total']--;
            $this->remainingStock[RefData::EMISSIONS_CATEGORY_EURO6_REF]['total']++;
        }

        // reduce the number of available permits for the requested Emissions Standard by 1
        $this->remainingStock[$emissionsCategoryId]['total']--;

        // record this candidate permit as requiring a permit for the requested Emissions Standard
        $suggestedEmissionsCategory = $emissionsCategory->getDescription();

        $suggestedCountries = [];

        if (!$restrictedCountries->isEmpty()) {
            // some restricted countries requested
            // order the list
            $criteria = Criteria::create()
                ->orderBy(['countryDesc' => Criteria::ASC]);

            foreach ($restrictedCountries->matching($criteria) as $country) {
                $countryId = $country->getId();

                if (!empty($this->remainingStock[$emissionsCategoryId][$countryId])) {
                    // we do have some stock left for the Restricted Country / Emissions Standard
                    // record this candidate permit as requiring a permit for the requested Restricted Country
                    $suggestedCountries[] = $country->getCountryDesc();

                    // reduce the number of available permits for the Restricted Country / Emissions Standard requested by 1
                    $this->remainingStock[$emissionsCategoryId][$countryId]--;
                } elseif (
                    $emissionsCategoryId == RefData::EMISSIONS_CATEGORY_EURO6_REF
                     && !empty($this->remainingStock[RefData::EMISSIONS_CATEGORY_EURO5_REF][$countryId])
                ) {
                    // no more Euro6 permits left for the restricted country
                    // but there are some Euro5 permits for the same country
                    // record this candidate permit as requiring a permit for the requested Restricted Country
                    $suggestedCountries[] = $country->getCountryDesc();

                    // reduce the number of available permits for the Restricted Country requested / Euro 5 by 1
                    $this->remainingStock[RefData::EMISSIONS_CATEGORY_EURO5_REF][$countryId]--;
                }
            }
        }

        if (empty($suggestedCountries)) {
            // no restricted countries suggested
            $suggestedCountries = [self::WITHOUT_RESTRICTED_COUNTRIES];
        }

        $key = sprintf(
            '%s-%s',
            $suggestedEmissionsCategory,
            implode('-', $suggestedCountries)
        );

        if (!isset($this->alignedStock[$key])) {
            $this->alignedStock[$key] = [
                'emissionsCategory' => $suggestedEmissionsCategory,
                'restrictedCountries' => implode(',', $suggestedCountries),
                'count' => 0,
            ];
        }

        $this->alignedStock[$key]['count'] += 1;
    }

    /**
     * Get a flattened array of aligned stock
     *
     * @return array
     */
    private function flattenAlignedStock()
    {
        $rows = [];

        // sort the array to make it easier to read
        ksort($this->alignedStock);

        // add header
        $rows[] = ['Emissions category', 'Restricted countries', 'Number of permits'];

        foreach ($this->alignedStock as $value) {
            $rows[] = [$value['emissionsCategory'], $value['restrictedCountries'], $value['count']];
        }

        return $rows;
    }
}
