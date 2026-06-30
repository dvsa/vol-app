<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/letter/issue-type/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
