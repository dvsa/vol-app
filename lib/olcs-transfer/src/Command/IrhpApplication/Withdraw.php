<?php

/**
 * Withdraw IRHP Permit Application
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\WithdrawApplicationInterface;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\PermitAppWithdrawReason;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-application/withdraw")
 * @Transfer\Method("POST")
 */
final class Withdraw extends AbstractCommand implements WithdrawApplicationInterface
{
    use Identity;
    use PermitAppWithdrawReason;
}
