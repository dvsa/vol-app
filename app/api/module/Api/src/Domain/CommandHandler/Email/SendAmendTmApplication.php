<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Email\AbstractSendTmEmail;

/**
 * Send Amend Transport Manager Application Email
 */
class SendAmendTmApplication extends AbstractSendTmEmail
{
    protected $template = 'transport-manager-amend-digital-form';
    protected $subject = 'email.transport-manager-amend-digital-form.subject';
}
