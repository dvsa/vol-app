<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class IrhpPermitApplicationCreator
{
    /**
     * Create service instance
     *
     *
     * @return IrhpPermitApplicationCreator
     */
    public function __construct(private readonly IrhpPermitStockRepository $irhpPermitStockRepo, private readonly IrhpPermitApplicationRepository $irhpPermitApplicationRepo, private readonly IrhpPermitApplicationFactory $irhpPermitApplicationFactory)
    {
    }

    /**
     * Handle the scenario where no irhp permit application exists for a country
     *
     * @param int $stockId
     */
    public function create(IrhpApplication $irhpApplication, $stockId)
    {
        $irhpPermitStock = $this->irhpPermitStockRepo->fetchById($stockId);

        $irhpPermitApplication = $this->irhpPermitApplicationFactory->create(
            $irhpApplication,
            $irhpPermitStock->getOpenWindow()
        );

        $this->irhpPermitApplicationRepo->save($irhpPermitApplication);

        return $irhpPermitApplication;
    }
}
