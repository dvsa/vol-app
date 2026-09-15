<?php

/**
 * Licence Update Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractUpdateAddresses;

/**
 * @Transfer\RouteName("backend/licence/single/addresses")
 * @Transfer\Method("PUT")
 */
final class UpdateAddresses extends AbstractUpdateAddresses
{
}
