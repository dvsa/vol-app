<?php

namespace Dvsa\Olcs\Api\Service\Permits\Fees;

use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepository;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;

class DaysToPayIssueFeeProvider
{
    /**
     * Create service instance
     *
     *
     * @return DaysToPayIssueFeeProvider
     */
    public function __construct(private readonly SystemParameterRepository $systemParameterRepo)
    {
    }

    /**
     * Return the number of weekdays in which the issue fee must be paid
     *
     * @return int
     */
    public function getDays()
    {
        $daysToPayIssueFee = $this->systemParameterRepo->fetchValue(SystemParameter::PERMITS_DAYS_TO_PAY_ISSUE_FEE);

        return (int)$daysToPayIssueFee;
    }
}
