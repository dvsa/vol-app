<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

class UserListInternalByTrafficArea extends AbstractListQueryHandler
{
    protected $repoServiceName = 'User';
    protected $bundle = [
        'team',
        'contactDetails' => [
            'person',
        ],
    ];
}
