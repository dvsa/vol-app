<?php

/**
 * Update IRHP Application
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitApplicationOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-application/update-check-answers")
 * @Transfer\Method("PUT")
 */
final class UpdateCheckAnswers extends AbstractCommand
{
    use Identity;
    use IrhpPermitApplicationOptional;
}
