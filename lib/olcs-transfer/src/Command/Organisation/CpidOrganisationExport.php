<?php

namespace Dvsa\Olcs\Transfer\Command\Organisation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/organisation/cpid")
 * @Transfer\Method("POST")
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class CpidOrganisationExport extends AbstractCommand
{
    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *         "haystack": {
     *             "op_cpid_central_government",
     *             "op_cpid_local_government",
     *             "op_cpid_public_corporation",
     *             "op_cpid_default",
     *             "op_cpid_all",
     *         }
     *     }
     * )
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
