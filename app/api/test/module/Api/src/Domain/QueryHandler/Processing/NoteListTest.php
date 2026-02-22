<?php

declare(strict_types=1);

/**
 * Note List Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Processing;

use Dvsa\Olcs\Api\Domain\QueryHandler\Processing\NoteList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Processing\NoteList as Qry;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * Note List Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NoteListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new NoteList();
        $this->mockRepo('Note', Repository\Note::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [
            'irhpApplication' => null,
            'case' => null,
            'busReg' => null,
            'application' => null
        ];

        $query = Qry::create($data);

        $mockNote = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['Note']->shouldReceive('disableSoftDeleteable')->once();
        $this->repoMap['Note']
            ->shouldReceive('fetchList')
            ->once()
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockNote]);
        $this->repoMap['Note']->shouldReceive('fetchCount')->with($query)->andReturn(5);
        $this->repoMap['Note']->shouldReceive('hasRows')->with(m::type(Qry::class))->andReturn(1);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [['foo' => 'bar']],
            'count' => 5,
            'count-unfiltered' => 1
        ];

        $this->assertEquals($expected, $result);
    }
}
