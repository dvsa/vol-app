<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractWithdrawApplicationHandler;

/**
 * Withdraw an IRHP Permit application
 *
 * @author Andy Newton <ian@hemera-business-services.co.uk>
 */
final class Withdraw extends AbstractWithdrawApplicationHandler
{
    protected $repoServiceName = 'IrhpApplication';
}
