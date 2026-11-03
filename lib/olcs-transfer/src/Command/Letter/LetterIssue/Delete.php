<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterIssue;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

#[Transfer\RouteName('backend/letter/letter-issue/single')]
#[Transfer\Method('DELETE')]
final class Delete extends AbstractDeleteCommand
{
}
