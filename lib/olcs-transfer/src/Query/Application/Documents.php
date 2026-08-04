<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategory;

/**
 * @Transfer\RouteName("backend/application/single/documents")
 */
class Documents extends AbstractQuery
{
    use Identity;
    use Category;
    use SubCategory;
}
