<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

/**
 * Revive IRHP Application from unsuccessful state
 */
final class ReviveFromUnsuccessful extends AbstractReviveFromUnsuccessful
{
    protected $repoServiceName = 'IrhpApplication';
}
