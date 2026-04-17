<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

final class SendLiquidatedCompanyForUnregisteredUser extends AbstractEmailOnlyCommandHandler
{
    #[\Override]
    protected function getEmailSubject(): string
    {
        return 'email.insolvent-company-notification.subject';
    }

    #[\Override]
    protected function getEmailTemplateName(): string
    {
        return 'insolvent-company-notification_unregistered-user';
    }
}
