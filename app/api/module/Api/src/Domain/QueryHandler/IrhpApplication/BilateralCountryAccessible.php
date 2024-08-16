<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible as BilateralCountryAccessibleQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Bilateral country accessible
 */
final class BilateralCountryAccessible extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|BilateralCountryAccessibleQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $countryId = $query->getCountry();
        $irhpApplicationId = $query->getId();

        $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);

        return [
            'isAccessible' => $this->isCountryAccessible($irhpApplication, $countryId)
        ];
    }

    /**
     * Whether the specified country id is accessible for the specified application
     *
     * @param string $countryId
     * @return bool
     */
    private function isCountryAccessible(IrhpApplication $irhpApplication, $countryId)
    {
        $countries = $irhpApplication->getCountrys();

        foreach ($countries as $country) {
            if ($country->getId() == $countryId) {
                return true;
            }
        }

        return false;
    }
}
