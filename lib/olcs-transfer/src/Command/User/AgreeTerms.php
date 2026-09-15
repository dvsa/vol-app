<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/user/selfserve/agree-terms")
 * @Transfer\Method("POST")
 */
final class AgreeTerms extends AbstractCommand
{
}
