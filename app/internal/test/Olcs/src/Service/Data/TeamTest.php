<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\Team;

/**
 * Class TeamTest
 * @package OlcsTest\Service\Data
 */
class TeamTest extends AbstractDataServiceTestCase
{
    /**
     * @dataProvider provideFetchListOptions
     */
    public function testFetchListOptions($input, $expected)
    {
        $sut = new Team();
        $sut->setData('teamlist', $input);

        $this->assertEquals($expected, $sut->fetchListOptions(''));
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
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->getMock();

        $sut = new Team();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchTeamListData());
        $this->assertEquals($results['results'], $sut->fetchTeamListData());  //ensure data is cached
    }

    public function testFetchLicenceDataWithError()
    {
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->once()
            ->andReturn(false)
            ->getMock();

        $sut = new Team();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->mockServiceLocator->shouldReceive('get')
            ->with('Helper\FlashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addErrorMessage')
                    ->with('unknown-error')
                    ->once()
                    ->getMock()
            );

        $sut->fetchTeamListData();
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
