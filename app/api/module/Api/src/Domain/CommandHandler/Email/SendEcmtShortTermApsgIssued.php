<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send confirmation of ECMT Short Term APSG permits being issued
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendEcmtShortTermApsgIssued extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-short-term-apsg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $extraRepos = ['FeeType'];
}
