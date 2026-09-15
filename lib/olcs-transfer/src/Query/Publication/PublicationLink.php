<?php

namespace Dvsa\Olcs\Transfer\Query\Publication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class PublicationLink
 * @Transfer\RouteName("backend/publication/link/single")
 */
class PublicationLink extends AbstractQuery
{
    use Identity;
}
