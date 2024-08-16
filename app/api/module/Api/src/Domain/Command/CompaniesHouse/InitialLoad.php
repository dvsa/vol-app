<?php

/**
 * InitialLoad
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\CompaniesHouse;

/**
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class InitialLoad extends \Dvsa\Olcs\Transfer\Command\AbstractCommand
{
    protected $companyNumber;

    /**
     * Gets the value of companyNumber.
     *
     * @return string
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }
}
