<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class CountryGenerator
{
    public const PERIOD_LABELS = [
        Behaviour::STANDARD => 'Select period',
        Behaviour::MOROCCO => 'Select stock',
    ];

    /**
     * Create service instance
     *
     *
     * @return CountryGenerator
     */
    public function __construct(private readonly PeriodArrayGenerator $periodArrayGenerator)
    {
    }

    /**
     * Generate the country part of the response
     *
     *
     * @return array
     */
    public function generate(Country $country, IrhpApplication $irhpApplication)
    {
        $countryId = $country->getId();
        $selectedPeriodId = null;

        $irhpPermitApplication = $irhpApplication->getIrhpPermitApplicationByStockCountryId($countryId);
        if (is_object($irhpPermitApplication)) {
            $selectedPeriodId = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getId();
        }

        $behaviour = Behaviour::STANDARD;
        if ($country->isMorocco()) {
            $behaviour = Behaviour::MOROCCO;
        }

        return [
            'id' => $countryId,
            'name' => $country->getCountryDesc(),
            'type' => $behaviour,
            'visible' => $irhpApplication->hasCountryId($countryId),
            'selectedPeriodId' => $selectedPeriodId,
            'periodLabel' => self::PERIOD_LABELS[$behaviour],
            'periods' => $this->periodArrayGenerator->generate($behaviour, $country, $irhpPermitApplication),
        ];
    }
}
