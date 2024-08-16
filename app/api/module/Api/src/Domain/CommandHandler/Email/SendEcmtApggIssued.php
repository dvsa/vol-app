<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send confirmation of ECMT Annual APGG permits being issued
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SendEcmtApggIssued extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-annual-apgg-app-issued';
    protected $subject = 'email.ecmt.issued.subject';
    protected $extraRepos = ['FeeType'];
}
