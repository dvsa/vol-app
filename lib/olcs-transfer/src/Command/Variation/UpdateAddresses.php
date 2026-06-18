<?php

/**
 * Variation Update Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractUpdateAddresses;

/**
 * @Transfer\RouteName("backend/variation/single/addresses")
 * @Transfer\Method("PUT")
 */
final class UpdateAddresses extends AbstractUpdateAddresses
{
}
