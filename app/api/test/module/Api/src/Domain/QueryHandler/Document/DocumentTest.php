<?php

declare(strict_types=1);

/**
 * Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\Document;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Transfer\Query\Document\Document as Qry;

/**
 * Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Document();
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $document = m::mock(\Dvsa\Olcs\Api\Entity\Doc\Document::class)->makePartial();
        $document->shouldReceive('serialize')
            ->with([])
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($document);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals(['foo' => 'bar'], $result->serialize());
    }
}
