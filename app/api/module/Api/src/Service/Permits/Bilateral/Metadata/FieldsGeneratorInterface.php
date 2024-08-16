<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;

interface FieldsGeneratorInterface
{
    /**
     * Generate the fields part of the response
     *
     * @param IrhpPermitApplication $irhpPermitApplication (optional)
     * @return array
     */
    public function generate(IrhpPermitStock $irhpPermitStock, ?IrhpPermitApplication $irhpPermitApplication);
}
