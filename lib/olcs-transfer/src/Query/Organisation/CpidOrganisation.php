<?php

namespace Dvsa\Olcs\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/organisation/cpid")
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class CpidOrganisation extends AbstractQuery implements PagedQueryInterface
{
    use PagedTrait;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"op_cpid_central_government", "op_cpid_local_government", "op_cpid_public_corporation", "op_cpid_default", "op_cpid_default", "op_cpid_all"}})
     */
    protected $cpid;

    /**
     * Get Cpid
     *
     * @return string
     */
    public function getCpid()
    {
        return $this->cpid;
    }
}
