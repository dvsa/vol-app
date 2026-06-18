<?php

/**
 * Get a paginated list of Document Templates
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\DocTemplate;

use Dvsa\Olcs\Transfer\FieldType\Traits\CategoryOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * @Transfer\RouteName("backend/doc-template/admin-list")
 */
final class FullList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use CategoryOptional;
}
