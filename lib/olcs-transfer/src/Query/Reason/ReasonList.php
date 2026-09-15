<?php

namespace Dvsa\Olcs\Transfer\Query\Reason;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Class ReasonList
 * @Transfer\RouteName("backend/reason")
 */
class ReasonList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use FieldType\GoodsOrPsvOptional;
    use FieldType\IsNiOptional;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $isProposeToRevoke;

    /**
     * @return string
     */
    public function getIsProposeToRevoke()
    {
        return $this->isProposeToRevoke;
    }
}
