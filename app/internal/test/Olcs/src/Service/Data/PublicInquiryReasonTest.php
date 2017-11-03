<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PublicInquiryReason;
use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Dvsa\Olcs\Transfer\Query\Reason\ReasonList as Qry;
use Mockery as m;

/**
 * Class PublicInquiryReasonTest
 * @package OlcsTest\Service\Data
 */
class PublicInquiryReasonTest extends AbstractDataServiceTestCase
{
    private $reasons = [
        ['id' => 12, 'sectionCode' => 'Section A', 'description' => 'Description 1'],
        ['id' => 15, 'sectionCode' => 'Section C', 'description' => 'Description 2'],
    ];

    public function testFetchListData()
    {
        $results = ['results' => $this->reasons];
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $sut = new PublicInquiryReason();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results, $sut->fetchListData([]));
    }


    public function testFetchPublicInquiryReasonDataFailure()
    {
        $results = [];
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')
            ->once()
            ->andReturn('query')
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $sut = new PublicInquiryReason();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEmpty($sut->fetchListData([]));
    }

    /**
     * @dataProvider provideFetchListOptions
     *
     * @param $niFlag
     * @param $goodsOrPsv
     * @param $expectedList
     */
    public function testFetchListOptions($niFlag, $goodsOrPsv, $expectedList)
    {
        $mockLicenceService = m::mock('\Common\Service\Data\Licence');
        $mockLicenceService
            ->shouldReceive('getId')
            ->once()
            ->andReturn(7)
            ->shouldReceive('fetchLicenceData')
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag'=> $niFlag,
                    'goodsOrPsv' => ['id'=> $goodsOrPsv],
                    'trafficArea' => ['id'=> 'B']
                ]
            )
            ->once()
            ->getMock();

        $results = ['results' => $expectedList];
        $params = [
            'sort' => 'sectionCode',
            'order' => 'ASC',
            'niFlag' => $niFlag,
            'goodsOrPsv' => $goodsOrPsv,
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    return 'query';
                }
            )
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $sut = new PublicInquiryReason();
        $sut->setLicenceService($mockLicenceService);

        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($this->getSingleExpected(), $sut->fetchListOptions($params));
    }

    /**
     * Data provider for testFetchListOptions
     *
     * @return array
     */
    public function provideFetchListOptions()
    {
        return [
            ['Y', 'lcat_psv', $this->getSingleSource()],
            ['N', 'lcat_psv', $this->getSingleSource()],
            ['Y', 'lcat_gv', $this->getSingleSource()],
            ['N', 'lcat_gv', $this->getSingleSource()]
        ];
    }

    /**
     * @return array
     */
    protected function getSingleExpected()
    {
        $expected = [
            'val-1' => 'Value 1',
            'val-2' => 'Value 2',
            'val-3' => 'Value 3',
        ];

        return $expected;
    }

    /**
     * @return array
     */
    protected function getSingleSource()
    {
        $source = [
            0 => ['id' => 'val-1', 'description' => 'Value 1'],
            1 => ['id' => 'val-2', 'description' => 'Value 2'],
            2 => ['id' => 'val-3', 'description' => 'Value 3']
        ];

        return $source;
    }
}
