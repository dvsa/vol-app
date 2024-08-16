<?php

/**
 * End IRHP applications relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\PermitAppWithdrawReason;

/**
 * End IRHP applications relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EndIrhpApplications extends AbstractCommand
{
    use Identity;
    use PermitAppWithdrawReason;
}
