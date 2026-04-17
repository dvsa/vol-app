<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgAppSubmitted as SendEcmtApsgAppSubmittedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApsgAppSubmitted as SendEcmtApsgAppSubmittedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the permit app APSG submitted email
 */
class SendEcmtApsgAppSubmittedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApsgAppSubmittedCmd::class;
    protected $commandHandlerClass = SendEcmtApsgAppSubmittedHandler::class;
    protected $template = 'ecmt-annual-apsg-app-submitted';
    protected $subject = 'email.ecmt.default.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
