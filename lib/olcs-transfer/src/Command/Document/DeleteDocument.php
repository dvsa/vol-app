<?php

/**
 * Delete Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/document/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteDocument extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $unlinkLicence = false;

    /**
     * @return mixed
     */
    public function getUnlinkLicence()
    {
        return $this->unlinkLicence;
    }
}
