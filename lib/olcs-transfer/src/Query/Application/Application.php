<?php

/**
 * Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/application/single")
 */
class Application extends AbstractQuery
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $validateAppCompletion;

    /**
     * Get a validate application completion flag
     *
     * @return bool
     */
    public function getValidateAppCompletion()
    {
        return $this->validateAppCompletion;
    }
}
