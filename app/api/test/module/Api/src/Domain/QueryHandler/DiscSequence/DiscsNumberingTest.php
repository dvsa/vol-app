<?php

declare(strict_types=1);

/**
 * Disc Numbering Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DiscSequence;

use Dvsa\Olcs\Api\Domain\QueryHandler\DiscSequence\DiscsNumbering as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as DiscSequenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc as PsvDiscRepo;
use Dvsa\Olcs\Transfer\Query\DiscSequence\DiscsNumbering as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Discs Numbering Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscsNumberingTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('DiscSequence', DiscSequenceRepo::class);
        $this->mockRepo('GoodsDisc', GoodsDiscRepo::class);
        $this->mockRepo('PsvDisc', PsvDiscRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyParamsProvider')]
    public function testHandleQueryEmpty(mixed $params): void
    {
        $query = Qry::create($params);
        $this->assertEquals(['result' => [], 'count' => 0], $this->sut->handleQuery($query));
    }

    public static function emptyParamsProvider(): array
    {
        return [
            [['operatorType' => 'lcat_gv', 'licenceType' => 'ltyp_r', 'discSequence' => 1]],
            [['niFlag' => 'N', 'licenceType' => 'ltyp_r', 'discSequence' => 1]],
            [['niFlag' => 'N', 'operatorType' => 'lcat_gv', 'discSequence' => 1]],
            [['niFlag' => 'N', 'operatorType' => 'lcat_gv', 'licenceType' => 'ltyp_r']]
        ];
    }

    public function testHandleQueryIncreaseStartNumber(): void
    {
        $operatorType = 'lcat_psv';
        $licenceType = 'ltyp_r';
        $discSequence = 1;
        $niFlag = 'N';
        $startNumberEntered = 2;
        $params = [
            'operatorType' => $operatorType,
            'licenceType' => $licenceType,
            'discSequence' => $discSequence,
            'niFlag' => $niFlag,
            'startNumberEntered' => $startNumberEntered
        ];
        $query = Qry::create($params);

        $this->repoMap['DiscSequence']
            ->shouldReceive('fetchById')
            ->with($discSequence)
            ->andReturn(
                m::mock()
                ->shouldReceive('getDiscNumber')
                ->with($licenceType)
                ->andReturn(1)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['PsvDisc']
            ->shouldReceive('fetchDiscsToPrintMin')
            ->with($licenceType)
            ->andReturn(['d1', 'd2', 'd3'])
            ->once()
            ->getMock();

        $expected = [
            'result' => [
                'startNumber' => 2,
                'discsToPrint' => 3,
                'endNumber' => 7,
                'originalEndNumber' => 3,
                'endNumberIncreased' => 4,
                'totalPages' => 1
            ],
            'count' => 6
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }

    public function testHandleQueryDecreaseStartNumber(): void
    {
        $operatorType = 'lcat_gv';
        $licenceType = 'ltyp_r';
        $discSequence = 1;
        $niFlag = 'N';
        $startNumberEntered = 2;
        $params = [
            'operatorType' => $operatorType,
            'licenceType' => $licenceType,
            'discSequence' => $discSequence,
            'niFlag' => $niFlag,
            'startNumberEntered' => $startNumberEntered
        ];
        $query = Qry::create($params);

        $this->repoMap['DiscSequence']
            ->shouldReceive('fetchById')
            ->with($discSequence)
            ->andReturn(
                m::mock()
                    ->shouldReceive('getDiscNumber')
                    ->with($licenceType)
                    ->andReturn(3)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['GoodsDisc']
            ->shouldReceive('fetchDiscsToPrintMin')
            ->with($niFlag, $licenceType)
            ->andReturn(['d1', 'd2', 'd3'])
            ->once()
            ->getMock();

        $expected = [
            'result' => [
                'startNumber' => 3,
                'discsToPrint' => 3,
                'endNumber' => 8,
                'originalEndNumber' => 5,
                'error' => 'Decreasing the start number is not permitted',
                'endNumberIncreased' => 5,
                'totalPages' => 1
            ],
            'count' => 7
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
