<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\Subjects;

use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Subjects\ById as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingSubject;
use Dvsa\Olcs\Transfer\Query\Messaging\Subjects\ById as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;

final class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = QueryHandler::class;
    protected $sutRepo = Repo::class;
    protected $bundle = ['category', 'subCategory'];
    protected $qryClass = Qry::class;
    protected $repoClass = Repo::class;
    protected $entityClass = MessagingSubject::class;
}
