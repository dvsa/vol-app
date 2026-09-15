<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\LoggerOmitResponseInterface;

/**
 * @Transfer\RouteName("backend/licence/single/export-psv-vehicles")
 */
class PsvVehiclesExport extends AbstractQuery implements CacheableShortTermQueryInterface, LoggerOmitResponseInterface
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $includeRemoved;

    /**
     * @return mixed
     */
    public function getIncludeRemoved()
    {
        return $this->includeRemoved;
    }
}
