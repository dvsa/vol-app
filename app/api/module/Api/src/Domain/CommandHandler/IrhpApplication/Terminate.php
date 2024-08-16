<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUpdateDefinedValue;
use Dvsa\Olcs\Api\Entity\IrhpInterface;

/**
 * Change IRHP Application status to terminated
 */
final class Terminate extends AbstractUpdateDefinedValue
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityMethodName = 'terminate';
    protected $definedValue = IrhpInterface::STATUS_TERMINATED;
    protected $isRefData = true;
}
