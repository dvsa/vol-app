<?php

declare(strict_types=1);

/**
 * Letter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\Letter;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Document\Letter as Qry;
use Dvsa\Olcs\Api\Entity;

/**
 * Letter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LetterTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Letter();
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('DocTemplate', Repository\DocTemplate::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        /** @var Entity\Doc\DocTemplate $docTemplate */
        $docTemplate = m::mock(Entity\Doc\DocTemplate::class)->makePartial();
        $docTemplate->shouldReceive('serialize')
            ->with([])
            ->andReturn(['foo' => 'bar']);

        /** @var Entity\Doc\Document $document */
        $document = m::mock(Entity\Doc\Document::class)->makePartial();
        $document->setMetadata('{"details":{"documentTemplate":"1"}}');
        $document->shouldReceive('serialize')
            ->with(
                [
                    'category',
                    'subCategory'
                ]
            )
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['Document']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($document);

        $this->repoMap['DocTemplate']->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($docTemplate);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals(['foo' => 'bar', 'template' => ['foo' => 'bar']], $result->serialize());
    }
}
