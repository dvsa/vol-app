<?php

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractPeople;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/licence/single/people')]
#[Transfer\Method('POST')]
final class CreatePeople extends AbstractPeople
{
}
