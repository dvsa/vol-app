<?php

/**
 * End IRHP permits relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\EndIrhpApplicationsAndPermitsContext;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * End IRHP permits relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class EndIrhpPermits extends AbstractCommand
{
    use Identity;
    use EndIrhpApplicationsAndPermitsContext;
}
