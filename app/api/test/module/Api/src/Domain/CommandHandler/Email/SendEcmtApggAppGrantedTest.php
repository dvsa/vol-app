<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggAppGranted as SendEcmtApggAppGrantedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApggAppGranted as SendEcmtApggAppGrantedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test ECMT APGG app granted email
 */
class SendEcmtApggAppGrantedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtApggAppGrantedCmd::class;
    protected $commandHandlerClass = SendEcmtApggAppGrantedHandler::class;
    protected $template = 'ecmt-annual-apgg-app-granted';
    protected $subject = 'email.ecmt.response.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
