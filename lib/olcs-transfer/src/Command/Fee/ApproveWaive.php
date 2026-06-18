<?php

/**
 * Approve Waive
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\Fee;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * @Transfer\RouteName("backend/fee/single/approve-waive")
 * @Transfer\Method("PUT")
 */
final class ApproveWaive extends AbstractCommand implements FieldType\IdentityInterface, FieldType\VersionInterface
{
    use FieldTypeTraits\Identity;
    use FieldTypeTraits\Version;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $waiveReason;

    /**
     * @return string
     */
    public function getWaiveReason()
    {
        return $this->waiveReason;
    }
}
