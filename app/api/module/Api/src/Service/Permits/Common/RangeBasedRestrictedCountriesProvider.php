<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepo;
use Dvsa\Olcs\Api\Service\Permits\Common\TypeBasedPermitTypeConfigProvider;

class RangeBasedRestrictedCountriesProvider
{
    /** @var array */
    private $restrictedCountries = [];

    /**
     * Create service instance
     *
     *
     * @return RangeBasedRestrictedCountriesProvider
     */
    public function __construct(private readonly IrhpPermitRangeRepo $irhpPermitRangeRepo, private readonly TypeBasedPermitTypeConfigProvider $typeBasedPermitTypeConfigProvider, private readonly CountryRepo $countryRepo)
    {
    }

    /**
     * Return the restricted countries
     *
     * @param int $irhpPermitRangeId
     *
     * @return array
     */
    public function getList($irhpPermitRangeId)
    {
        if (!isset($this->restrictedCountries[$irhpPermitRangeId])) {
            // fetch the list by ids
            $this->restrictedCountries[$irhpPermitRangeId] = $this->countryRepo->fetchByIds(
                $this->getIds($irhpPermitRangeId),
                Query::HYDRATE_ARRAY
            );
        }

        return $this->restrictedCountries[$irhpPermitRangeId];
    }

    /**
     * Return the restricted country ids
     *
     * @param int $irhpPermitRangeId
     *
     * @return array
     */
    private function getIds($irhpPermitRangeId)
    {
        $irhpPermitRange = $this->irhpPermitRangeRepo->fetchById($irhpPermitRangeId);

        // get ids of restricted countries based on type
        $irhpPermitTypeId = $irhpPermitRange->getIrhpPermitStock()->getIrhpPermitType()->getId();
        $permitTypeConfig = $this->typeBasedPermitTypeConfigProvider->getPermitTypeConfig($irhpPermitTypeId);
        $restrictedCountryIds = $permitTypeConfig->getRestrictedCountryIds();

        // get ids of countries included in the range
        $includedCountryIds = array_map(
            fn($country) => $country->getId(),
            $irhpPermitRange->getCountrys()->toArray()
        );

        // if a restricted country is not specifically included in the range it is a constrained country
        return array_diff($restrictedCountryIds, $includedCountryIds);
    }
}
