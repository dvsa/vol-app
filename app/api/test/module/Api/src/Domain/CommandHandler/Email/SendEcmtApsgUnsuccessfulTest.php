<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgUnsuccessful as SendEcmtApsgUnsuccessfulCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApsgUnsuccessful as SendEcmtApsgUnsuccessfulHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the permit app APSG unsuccessful email
 */
class SendEcmtApsgUnsuccessfulTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApsgUnsuccessfulCmd::class;
    protected $commandHandlerClass = SendEcmtApsgUnsuccessfulHandler::class;
    protected $template = 'ecmt-annual-apsg-app-unsuccessful';
    protected $subject = 'email.ecmt.response.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
