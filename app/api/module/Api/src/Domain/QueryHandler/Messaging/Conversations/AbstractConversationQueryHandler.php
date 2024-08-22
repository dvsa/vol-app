<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations;

use ArrayIterator;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\User\Role;

abstract class AbstractConversationQueryHandler extends AbstractQueryHandler
{
    protected const STATUS_CLOSED = "CLOSED";
    protected const STATUS_NEW_MESSAGE = "NEW_MESSAGE";
    protected const STATUS_OPEN = "OPEN";

    protected function stringifyMessageStatus(array $conversation, bool $hasUnread): string
    {
        if ($conversation['isClosed']) {
            return self::STATUS_CLOSED;
        }
        if ($hasUnread) {
            return self::STATUS_NEW_MESSAGE;
        }
        return self::STATUS_OPEN;
    }

    protected function getFilteringRoles(): array
    {
        if ($this->getUser()->isInternal()) {
            return [
                Role::ROLE_SYSTEM_ADMIN,
                Role::ROLE_INTERNAL_ADMIN,
                Role::ROLE_INTERNAL_CASE_WORKER,
                Role::ROLE_INTERNAL_IRHP_ADMIN,
                Role::ROLE_INTERNAL_READ_ONLY,
            ];
        } else {
            return [
                Role::ROLE_OPERATOR_TC,
                Role::ROLE_OPERATOR_ADMIN,
                Role::ROLE_OPERATOR_USER,
            ];
        }
    }
}
