<?php

namespace Dvsa\Olcs\Transfer\Query\System\InfoMessage;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/system-info-message/active")
 */
class GetListActive extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $isInternal;

    /**
     * @return mixed
     */
    public function isInternal()
    {
        return $this->isInternal;
    }
}
