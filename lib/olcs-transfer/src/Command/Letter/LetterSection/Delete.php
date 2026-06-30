<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterSection;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/letter/letter-section/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
