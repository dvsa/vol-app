<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgSuccessful as SendEcmtApsgSuccessfulCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApsgSuccessful as SendEcmtApsgSuccessfulHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the permit app APSG successful email
 */
class SendEcmtApsgSuccessfulTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApsgSuccessfulCmd::class;
    protected $commandHandlerClass = SendEcmtApsgSuccessfulHandler::class;
    protected $template = 'ecmt-annual-apsg-app-successful';
    protected $subject = 'email.ecmt.response.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
