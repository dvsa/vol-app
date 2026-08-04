<?php

/**
 * Refund Fee
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\Fee;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * @Transfer\RouteName("backend/fee/single/refund-fee")
 * @Transfer\Method("PUT")
 */
final class RefundFee extends AbstractCommand implements FieldType\IdentityInterface
{
    use FieldTypeTraits\Identity;
    use FieldTypeTraits\MiscFeesDetails;
}
