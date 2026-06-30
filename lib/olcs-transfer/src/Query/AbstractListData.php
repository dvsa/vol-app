<?php

/**
 * Abstract List Data
 */

namespace Dvsa\Olcs\Transfer\Query;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Abstract List Data
 */
class AbstractListData extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTraitOptional;
}
