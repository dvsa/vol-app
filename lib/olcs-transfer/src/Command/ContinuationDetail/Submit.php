<?php

namespace Dvsa\Olcs\Transfer\Command\ContinuationDetail;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/continuation-detail/single/submit")
 * @Transfer\Method("PUT")
 */
final class Submit extends AbstractCommand
{
    use Identity;
    use Version;
}
