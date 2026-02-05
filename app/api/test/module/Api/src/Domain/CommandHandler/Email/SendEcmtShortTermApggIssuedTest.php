<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApggIssued as SendEcmtShortTermApggIssuedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtShortTermApggIssued as SendEcmtShortTermApggIssuedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the ECMT Short Term APGG issued email
 */
class SendEcmtShortTermApggIssuedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtShortTermApggIssuedCmd::class;
    protected $commandHandlerClass = SendEcmtShortTermApggIssuedHandler::class;
    protected $template = 'ecmt-short-term-apgg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
