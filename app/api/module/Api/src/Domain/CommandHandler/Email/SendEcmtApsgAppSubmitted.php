<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send ECMT APSG app submitted email
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendEcmtApsgAppSubmitted extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-annual-apsg-app-submitted';
    protected $subject = 'email.ecmt.default.subject';
    protected $extraRepos = ['FeeType'];
}
