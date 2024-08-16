<?php

/**
 * DeleteApplication.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Class DeleteApplication
 * @package Dvsa\Olcs\Api\Domain\Command\Application
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteApplication extends AbstractCommand
{
    use Identity;
}
