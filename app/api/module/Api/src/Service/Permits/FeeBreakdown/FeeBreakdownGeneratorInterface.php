<?php

namespace Dvsa\Olcs\Api\Service\Permits\FeeBreakdown;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

interface FeeBreakdownGeneratorInterface
{
    /**
     * Get a presentation-neutral fee breakdown representation for this application
     *
     *
     * @return array
     */
    public function generate(IrhpApplication $irhpApplication);
}
