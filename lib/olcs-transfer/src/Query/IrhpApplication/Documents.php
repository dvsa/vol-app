<?php

/**
 * Documents
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategory;

/**
 * @Transfer\RouteName("backend/irhp-application/documents")
 */
class Documents extends AbstractQuery
{
    use Identity;
    use Category;
    use SubCategory;
}
