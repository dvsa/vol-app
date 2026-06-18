<?php

/**
 * Markers, get the markers that should be shown for a Licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;

/**
 * @Transfer\RouteName("backend/licence/single/markers")
 */
class Markers extends AbstractQuery implements CacheableShortTermQueryInterface
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
