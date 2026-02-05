<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Common\Service\Helper\FlashMessengerHelperService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Team\TeamListData as Qry;
use Mockery as m;
use Olcs\Service\Data\Team;
use PHPUnit\Framework\Attributes\DataProvider;

class TeamTest extends AbstractDataServiceTestCase
{
    private Team $sut;

    /** @var  m\MockInterface */
    protected $flashMessengerHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashMessengerHelper = m::mock(FlashMessengerHelperService::class);

        $this->sut = new Team(
            $this->abstractDataServiceServices,
            $this->flashMessengerHelper
        );
    }

    #[DataProvider('provideFetchListOptions')]
    public function testFetchListOptions(mixed $input, mixed $expected): void
    {
        $this->sut->setData('teamlist', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    public static function provideFetchListOptions(): array
    {
        return [
            [
                [
                    ['id' => 'val-1', 'name' => 'Value 1'],
                    ['id' => 'val-2', 'name' => 'Value 2'],
                    ['id' => 'val-3', 'name' => 'Value 3'],
                ],
                self::SINGLE_EXPECTED,
            ],
            [false, []]
        ];
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchTeamListData());
        $this->assertEquals($results['results'], $this->sut->fetchTeamListData());  //ensure data is cached
    }

    public function testFetchLicenceDataWithError(): void
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->flashMessengerHelper->shouldReceive('addErrorMessage')
            ->with('unknown-error')
            ->once();

        $this->sut->fetchTeamListData();
    }
}
