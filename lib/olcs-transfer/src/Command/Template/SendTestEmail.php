<?php

/**
 * Send a one-shot test email for a `format='md'` template via the dedicated NotifyTestMailer.
 *
 * Used by the admin CMS at /admin/email-templates so admins can verify the Notify rendering
 * of a converted template before the env-level mail DSN cutover.
 */

namespace Dvsa\Olcs\Transfer\Command\Template;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/template/send-test-email")
 * @Transfer\Method("POST")
 */
final class SendTestEmail extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Validator("Laminas\Validator\EmailAddress")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @var string
     */
    protected $recipient;

    public function getRecipient(): string
    {
        return (string) $this->recipient;
    }
}
