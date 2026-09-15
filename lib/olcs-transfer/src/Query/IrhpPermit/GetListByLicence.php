<?php

/**
 * Get a list of IRHP Permits by Licence id
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;

/**
 * @Transfer\RouteName("backend/irhp-permits/by-licence")
 */
final class GetListByLicence extends AbstractQuery implements PagedQueryInterface
{
    use PagedTrait;
    use Traits\Licence;
    use Traits\IrhpPermitTypeOptional;
    use Traits\CountryOptional;
    use Traits\IrhpPermitStatusOptional;
    use Traits\ValidOnlyOptional;
}
