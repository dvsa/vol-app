<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\Query\Document\ByDocumentStoreId as Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\ByDocumentStoreId as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * ByDocumentStoreId Test
 */
class ByDocumentStoreIdTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $documentStoreId = 'ABC';

        $query = Query::create(
            [
                'documentStoreId' => $documentStoreId,
            ]
        );

        $results = [
            ['id' => 1],
            ['id' => 2],
        ];

        $this->repoMap['Document']
            ->shouldReceive('fetchByDocumentStoreId')
            ->with($documentStoreId)
            ->once()
            ->andReturn($results);

        self::assertEquals($results, $this->sut->handleQuery($query));
    }
}
