<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;

class PeriodArrayGenerator
{
    /**
     * Create service instance
     *
     *
     * @return PeriodArrayGenerator
     */
    public function __construct(private readonly IrhpPermitStockRepository $irhpPermitStockRepo, private readonly PeriodGenerator $periodGenerator, private readonly CurrentDateTimeFactory $currentDateTimeFactory)
    {
    }

    /**
     * Generate the period list part of the response
     *
     * @param string $behaviour
     * @param IrhpPermitApplication $irhpPermitApplication (optional)
     * @return array
     */
    public function generate($behaviour, Country $country, ?IrhpPermitApplication $irhpPermitApplication)
    {
        $stocks = $this->irhpPermitStockRepo->fetchOpenBilateralStocksByCountry(
            $country->getId(),
            $this->currentDateTimeFactory->create()
        );

        $responsePeriods = [];

        foreach ($stocks as $stock) {
            $responsePeriods[] = $this->periodGenerator->generate($stock['id'], $behaviour, $irhpPermitApplication);
        }

        return $responsePeriods;
    }
}
