<?php

/**
 * Delete User
 */

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/user/internal/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteUser extends AbstractDeleteCommand
{
}
