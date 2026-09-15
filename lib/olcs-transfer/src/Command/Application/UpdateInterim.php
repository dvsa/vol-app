<?php

/**
 * Update Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractUpdateInterim;

/**
* @Transfer\RouteName("backend/application/single/interim")
* @Transfer\Method("PUT")
*/
final class UpdateInterim extends AbstractUpdateInterim
{
}
