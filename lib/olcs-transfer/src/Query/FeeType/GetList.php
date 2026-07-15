<?php

/**
 * Get a list of Fee Types
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\FeeType;

use Dvsa\Olcs\Transfer\FieldType\Traits\FeeTypeOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\GoodsOrPsvOptional;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/fee-type/fee-rates")
 */
final class GetList extends AbstractQuery implements OrderedQueryInterface, PagedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use GoodsOrPsvOptional;
    use FeeTypeOptional;

    /**
     * @var bool
     */
    public $isFeeRateAdmin = true;

    /**
     * @return bool
     */
    public function getIsFeeRateAdmin()
    {
        return $this->isFeeRateAdmin;
    }
}
