<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Subjects;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;

final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = Repo::class;
    protected $bundle = ['category', 'subCategory'];
}
