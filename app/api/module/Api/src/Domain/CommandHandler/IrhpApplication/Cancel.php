<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCancelApplicationHandler;
use Dvsa\Olcs\Api\Entity\IrhpInterface;

/**
 * Cancel an IRHP permit application
 *
 * @author Ian Linday <ian@hemera-business-services.co.uk>
 */
final class Cancel extends AbstractCancelApplicationHandler
{
    protected $repoServiceName = 'IrhpApplication';
    protected $cancelStatus = IrhpInterface::STATUS_CANCELLED;
}
