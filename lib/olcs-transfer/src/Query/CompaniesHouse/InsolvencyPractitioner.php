<?php

namespace Dvsa\Olcs\Transfer\Query\CompaniesHouse;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/** *
 * @Transfer\RouteName("backend/companies-house/insolvency-practitioner")
 * @Transfer\Method("GET")
 */
class InsolvencyPractitioner extends AbstractQuery
{
    use Identity;
}
