<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPartSuccessful as SendEcmtApsgPartSuccessfulCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApsgPartSuccessful as SendEcmtApsgPartSuccessfulHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the permit app APSG part successful email
 */
class SendEcmtApsgPartSuccessfulTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApsgPartSuccessfulCmd::class;
    protected $commandHandlerClass = SendEcmtApsgPartSuccessfulHandler::class;
    protected $template = 'ecmt-annual-apsg-app-part-successful';
    protected $subject = 'email.ecmt.response.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
