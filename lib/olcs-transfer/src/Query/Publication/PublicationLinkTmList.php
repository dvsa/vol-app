<?php

namespace Dvsa\Olcs\Transfer\Query\Publication;

use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManager;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class PublicationLinkTmList
 * @Transfer\RouteName("backend/publication/link/tm-list")
 */
class PublicationLinkTmList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use TransportManager;
}
