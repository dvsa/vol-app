<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send confirmation of ECMT Annual APSG permits being issued
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendEcmtApsgIssued extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-annual-apsg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $extraRepos = ['FeeType'];
}
