<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Mockery as m;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
abstract class AbstractListQueryHandlerTestCase extends QueryHandlerTestCase
{
    /** @var  string */
    protected $sutClass;

    /** @var  string */
    protected $qryClass;

    /** @var  string */
    protected $repoClass;

    /** @var  string */
    protected $sutRepo;

    protected bool $modifiesTrafficAreasForRbac = false;

    /** @var QueryHandler\QueryHandlerInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new $this->sutClass();

        $this->mockRepo($this->sutRepo, $this->repoClass);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        /**
         * @var TransferQry\QueryInterface $qry
         * @var TransferQry\QueryInterface $query
         *
         * Once we're on PHP 7 we can switch to $query = $this->qryClass::create([]); instead
         */
        $qry = $this->qryClass;
        $query = $qry::create([]);

        if ($this->modifiesTrafficAreasForRbac) {
            $userData = [
                'dataAccess' => [
                    'trafficAreas' => ['A', 'B'],
                ],
            ];

            $this->expectedUserDataCacheCall($userData);
        }

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('EXPECT');

        $this->repoMap[$this->sutRepo]
            ->shouldReceive('fetchList')->with($query, Query::HYDRATE_OBJECT)->andReturn([$mockResult])
            ->shouldReceive('fetchCount')->with($query)->andReturn(2);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(2, $result['count']);
        $this->assertEquals(['EXPECT'], $result['result']);
    }
}
