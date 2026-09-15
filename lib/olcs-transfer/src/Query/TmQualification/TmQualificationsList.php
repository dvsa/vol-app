<?php

namespace Dvsa\Olcs\Transfer\Query\TmQualification;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;

/**
 * Class TmQualificationsList
 *
 * @Transfer\RouteName("backend/tm-qualification/transport-manager/named-single")
 */
class TmQualificationsList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManager;

    /**
     * @return string
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }
}
