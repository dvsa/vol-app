<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggIssued as SendEcmtApggIssuedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApggIssued as SendEcmtApggIssuedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the ECMT Annual APGG issued email
 */
class SendEcmtApggIssuedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApggIssuedCmd::class;
    protected $commandHandlerClass = SendEcmtApggIssuedHandler::class;
    protected $template = 'ecmt-annual-apgg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
