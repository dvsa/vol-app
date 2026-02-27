<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgIssued as SendEcmtApsgIssuedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApsgIssued as SendEcmtApsgIssuedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the ECMT Annual APSG issued email
 */
class SendEcmtApsgIssuedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApsgIssuedCmd::class;
    protected $commandHandlerClass = SendEcmtApsgIssuedHandler::class;
    protected $template = 'ecmt-annual-apsg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
