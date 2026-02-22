<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention\Records as RecordsHandler;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\DataRetention\Records as RecordsQuery;
use Dvsa\Olcs\Api\Domain\Repository\DataRetention as DataRetentionRepo;

/**
 * Records Test
 */
class RecordsTest extends AbstractListQueryHandlerTestCase
{
    protected $sutClass = RecordsHandler::class;
    protected $sutRepo = 'DataRetention';
    protected $qryClass = RecordsQuery::class;
    protected $repoClass = DataRetentionRepo::class;
}
