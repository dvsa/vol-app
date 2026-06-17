<?php

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractPeople;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/licence/single/people")
 * @Transfer\Method("POST")
 */
final class CreatePeople extends AbstractPeople
{
}
