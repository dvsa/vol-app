<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermApsgIssued as SendEcmtShortTermApsgIssuedCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtShortTermApsgIssued as SendEcmtShortTermApsgIssuedHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

/**
 * Test the ECMT Short Term APSG issued email
 */
class SendEcmtShortTermApsgIssuedTest extends AbstractEcmtAnnualPermitTestCase
{
    protected $commandClass = SendEcmtShortTermApsgIssuedCmd::class;
    protected $commandHandlerClass = SendEcmtShortTermApsgIssuedHandler::class;
    protected $template = 'ecmt-short-term-apsg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;
}
