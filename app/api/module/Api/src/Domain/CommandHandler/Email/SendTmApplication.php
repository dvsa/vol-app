<?php

/**
 * Send Transport Manager Application Email
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Email\AbstractSendTmEmail;

/**
 * Send Transport Manager Application Email
 */
final class SendTmApplication extends AbstractSendTmEmail
{
    protected $template = 'transport-manager-complete-digital-form';
    protected $subject = 'email.transport-manager-complete-digital-form.subject';
}
