<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

class UserListByTrafficArea extends AbstractListQueryHandler
{
    protected $repoServiceName = 'User';
    protected $bundle =                 [
        'contactDetails' => [
            'person',
        ],
        'roles',
    ];
}
