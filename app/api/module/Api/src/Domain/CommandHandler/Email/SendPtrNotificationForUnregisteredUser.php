<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

class SendPtrNotificationForUnregisteredUser extends AbstractEmailOnlyCommandHandler
{
    #[\Override]
    protected function getEmailSubject(): string
    {
        return 'email.insolvent-company-notification.subject';
    }

    #[\Override]
    protected function getEmailTemplateName(): string
    {
        return 'ptr-notification_unregistered-user';
    }
}
