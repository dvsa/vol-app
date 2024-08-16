<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUpdateDefinedValue;
use Dvsa\Olcs\Api\Entity\IrhpInterface;

/**
 * Change IRHP Application status to expired
 */
final class Expire extends AbstractUpdateDefinedValue
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityMethodName = 'expire';
    protected $definedValue = IrhpInterface::STATUS_EXPIRED;
    protected $isRefData = true;
}
