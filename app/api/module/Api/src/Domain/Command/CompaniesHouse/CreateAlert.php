<?php

namespace Dvsa\Olcs\Api\Domain\Command\CompaniesHouse;

/**
 * CreateAlert
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class CreateAlert extends \Dvsa\Olcs\Transfer\Command\AbstractCommand
{
    /**
     * @var string
     */
    protected $companyNumber;

    /**
     * @var array
     */
    protected $reasons = [];

    /**
     * Gets the value of companyNumber.
     *
     * @return string
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    /**
     * Gets the value of reasons.
     *
     * @return array
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
