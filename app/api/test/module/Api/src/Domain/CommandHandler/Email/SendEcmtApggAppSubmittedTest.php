<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggAppSubmitted as SendEcmtApggAppSubmittedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApggAppSubmitted as SendEcmtApggAppSubmittedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the permit app APGG submitted email
 */
class SendEcmtApggAppSubmittedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApggAppSubmittedCmd::class;
    protected $commandHandlerClass = SendEcmtApggAppSubmittedHandler::class;
    protected $template = 'ecmt-annual-apgg-app-submitted';
    protected $subject = 'email.ecmt.default.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
