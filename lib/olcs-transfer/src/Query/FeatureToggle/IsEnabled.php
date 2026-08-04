<?php

/**
 * Get a single feature toggle by id
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\FeatureToggle;

use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/feature-toggle/check")
 */
class IsEnabled extends AbstractQuery implements CacheableMediumTermQueryInterface, PublicQueryCacheInterface
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    protected $ids = [];

    public function getIds(): array
    {
        return $this->ids;
    }
}
