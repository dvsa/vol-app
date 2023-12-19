<?php

namespace OlcsTest\Service\Data;

use Common\Service\Helper\FlashMessengerHelperService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Team\TeamListData as Qry;
use Mockery as m;
use Olcs\Service\Data\Team;

/**
 * Class TeamTest
 * @package OlcsTest\Service\Data
 */
class TeamTest extends AbstractDataServiceTestCase
{
    /** @var Team */
    private $sut;

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

    /**
     * @dataProvider provideFetchListOptions
     */
    public function testFetchListOptions($input, $expected)
    {
        $this->sut->setData('teamlist', $input);

        $this->assertEquals($expected, $this->sut->fetchListOptions(''));
    }

    public function provideFetchListOptions()
    {
        return [
            [$this->getSingleSource(), $this->getSingleExpected()],
            [false, []]
        ];
    }

    public function testFetchListData()
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

    public function testFetchLicenceDataWithError()
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

    protected function getSingleExpected()
    {
        return [
            '1' => 'Development',
            '5' => 'Some other team',
        ];
    }

    protected function getSingleSource()
    {
        return [
            ['id' => 1, 'name' => 'Development'],
            ['id' => 5, 'name' => 'Some other team'],
        ];
    }
}
