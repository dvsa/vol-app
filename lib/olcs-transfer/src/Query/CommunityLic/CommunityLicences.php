<?php

namespace Dvsa\Olcs\Transfer\Query\CommunityLic;

use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class History
 * @Transfer\RouteName("backend/community-lic/list")
 */
class CommunityLicences extends AbstractQuery implements
    OrderedQueryInterface,
    PagedQueryInterface
{
    use OrderedTrait;
    use LicenceOptional;
    use PagedTrait;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @transfer\Optional
     */
    protected $statuses;

    /**
     * Get statuses
     *
     * @return string
     */
    public function getStatuses()
    {
        return $this->statuses;
    }
}
