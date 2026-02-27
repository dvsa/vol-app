<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtAutomaticallyWithdrawn as SendEcmtAutomaticallyWithdrawnCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtAutomaticallyWithdrawn as SendEcmtAutomaticallyWithdrawnHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the permit app successful email
 */
class SendEcmtAutomaticallyWithdrawnTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtAutomaticallyWithdrawnCmd::class;
    protected $commandHandlerClass = SendEcmtAutomaticallyWithdrawnHandler::class;
    protected $template = 'ecmt-automatically-withdrawn';
    protected $subject = 'email.ecmt.automatically.withdrawn.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
