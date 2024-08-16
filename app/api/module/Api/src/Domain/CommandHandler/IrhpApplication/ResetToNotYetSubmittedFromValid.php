<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUpdateDefinedValue;
use Dvsa\Olcs\Api\Entity\IrhpInterface;

/**
 * Reset to NotYetSubmitted from Valid
 */
final class ResetToNotYetSubmittedFromValid extends AbstractUpdateDefinedValue
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityMethodName = 'resetToNotYetSubmittedFromValid';
    protected $definedValue = IrhpInterface::STATUS_NOT_YET_SUBMITTED;
    protected $isRefData = true;
}
