<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EcmtAnnualPermitEmailTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;

/**
 * Send confirmation of ECMT app being automatically withdrawn
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class SendEcmtAutomaticallyWithdrawn extends AbstractEmailHandler
{
    use EcmtAnnualPermitEmailTrait;
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-automatically-withdrawn';
    protected $subject = 'email.ecmt.automatically.withdrawn.subject';
    protected $extraRepos = ['FeeType'];
}
