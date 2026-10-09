<?php

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractPeople;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/application/single/people')]
#[Transfer\Method('POST')]
final class CreatePeople extends AbstractPeople
{
}
