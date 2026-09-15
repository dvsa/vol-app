<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/licence/single/psv-discs")
 */
class PsvDiscs extends AbstractQuery implements \Dvsa\Olcs\Transfer\Query\PagedQueryInterface
{
    use Identity;
    use \Dvsa\Olcs\Transfer\Query\PagedTraitOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $includeCeased = false;

    /**
     * @return boolean
     */
    public function getIncludeCeased()
    {
        return $this->includeCeased;
    }
}
