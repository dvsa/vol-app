<?php

namespace Dvsa\Olcs\Transfer\Command\Organisation;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/organisation/generate-name")
 * @Transfer\Method("POST")
 */
final class GenerateName extends AbstractCommand
{
    use Traits\ApplicationOptional;
    use Traits\Organisation;
}
