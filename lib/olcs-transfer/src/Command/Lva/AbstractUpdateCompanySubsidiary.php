<?php

namespace Dvsa\Olcs\Transfer\Command\Lva;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Save (Create/Update) Company Subsidiary
 *
 * @author Dmitry Golubev <dmitrijs.golubev@valtech.co.uk>
 */
abstract class AbstractUpdateCompanySubsidiary extends AbstractCreateCompanySubsidiary
{
    use Traits\Identity;
    use Traits\Version;
}
