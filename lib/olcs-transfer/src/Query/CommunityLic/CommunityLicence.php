<?php

namespace Dvsa\Olcs\Transfer\Query\CommunityLic;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class CommunityLicence
 * @Transfer\RouteName("backend/community-lic/single")
 */
class CommunityLicence extends AbstractQuery
{
    use Identity;
}
