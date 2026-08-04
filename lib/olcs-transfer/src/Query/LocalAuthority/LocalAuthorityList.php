<?php

namespace Dvsa\Olcs\Transfer\Query\LocalAuthority;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class LocalAuthorityList
 * @Transfer\RouteName("backend/local-authority")
 */
class LocalAuthorityList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTraitOptional;
    use OrderedTraitOptional;
}
