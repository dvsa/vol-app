<?php

/**
 * Delete User Selfserve
 */

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/user/selfserve/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteUserSelfserve extends AbstractDeleteCommand
{
}
