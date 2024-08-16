<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send ECMT APGG app submitted email
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SendEcmtApggAppSubmitted extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-annual-apgg-app-submitted';
    protected $subject = 'email.ecmt.default.subject';
    protected $extraRepos = ['FeeType'];
}
