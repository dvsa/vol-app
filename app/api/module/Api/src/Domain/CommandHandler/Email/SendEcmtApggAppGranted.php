<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send confirmation of ECMT APGG app being granted
 */
class SendEcmtApggAppGranted extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-annual-apgg-app-granted';
    protected $subject = 'email.ecmt.response.subject';
    protected $extraRepos = ['FeeType'];
}
