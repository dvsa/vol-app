<?php

/**
 * Get a list of IRHP Permit Applications
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitApplication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * @Transfer\RouteName("backend/irhp-permit-application")
 */
final class GetList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
    use FieldTypeTraits\Licence;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $status = null;

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $onlyIssued = false;

    /**
     * @return bool
     */
    public function getOnlyIssued()
    {
        return $this->onlyIssued;
    }
}
