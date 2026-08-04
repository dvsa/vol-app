<?php

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractUpdateInterim;

/**
 * @Transfer\RouteName("backend/variation/single/interim")
 * @Transfer\Method("PUT")
 */
final class UpdateInterim extends AbstractUpdateInterim
{
}
