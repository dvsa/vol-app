<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Prohibition;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class Prohibition List
 *
 * @Transfer\RouteName("backend/prohibition")
 */
class ProhibitionList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use FieldTypeTraits\Cases;
}
