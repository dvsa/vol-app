<?php

/**
 * Update Irhp Application
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationCheckedOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CorCertificateNumberOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\DateReceived;
use Dvsa\Olcs\Transfer\FieldType\Traits\Declaration;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\MultipleNoOfPermitsOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\PostDataOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-application/full")
 * @Transfer\Method("PUT")
 */
final class UpdateFull extends AbstractCommand
{
    use Identity;
    use DateReceived;
    use MultipleNoOfPermitsOptional;
    use Declaration;
    use PostDataOptional;
    use ApplicationCheckedOptional;
    use CorCertificateNumberOptional;
}
