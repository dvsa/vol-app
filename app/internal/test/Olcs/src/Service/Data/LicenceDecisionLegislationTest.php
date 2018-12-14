<?php

/**
 * LicenceDecisionLegislation data service test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\LicenceDecisionLegislation;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Decision\DecisionList as Qry;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * LicenceDecisionLegislation data service test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class LicenceDecisionLegislationTest extends AbstractDataServiceTestCase
{
    private $context = [
        'isNi' => 'Y',
        'goodsOrPsv' => 'goods'
    ];

    private $listData = [
        [
            'id' => 5,
            'sectionCode' => 'Section 1',
            'description' => 'leg 1'
        ],
        [
            'id' => 9,
            'sectionCode' => 'Section 1',
            'description' => 'leg 2'
        ],
        [
            'id' => 6,
            'sectionCode' => 'Section 3',
            'description' => 'leg 3'
        ]
    ];

    /**
     * Test fetchListData
     */
    public function testFetchListData()
    {
        $results = ['results' => 'results'];

        $params = $this->context;
        $dto = Qry::create($this->context);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals('sectionCode', $dto->getSort());
                    $this->assertEquals('ASC', $dto->getOrder());
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
            ->twice()
            ->getMock();

        $sut = new LicenceDecisionLegislation();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchListData($this->context));

        // test cached results
        $this->assertEquals($results['results'], $sut->fetchListData($this->context));
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsUsingGroups()
    {
        $sut = new LicenceDecisionLegislation();
        $sut->setData('licenceDecisionLegislation', $this->listData);

        // tests formatGroups is called to give the array structure below
        $this->assertEquals(
            [
                'Section 1' => [
                    'label' => 'Section 1',
                    'options' => [
                        5 => 'leg 1',
                        9 => 'leg 2'
                    ]
                ],
                'Section 3' => [
                    'label' => 'Section 3',
                    'options' => [
                        6 => 'leg 3'
                    ]
                ]
            ],
            $sut->fetchListOptions($this->context, true)
        );
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsWithoutGroups()
    {
        $sut = new LicenceDecisionLegislation();
        $sut->setData('licenceDecisionLegislation', $this->listData);

        // tests formatGroups is called to give the array structure below
        $this->assertEquals(
            [
                5 => 'Section 1 - leg 1',
                9 => 'Section 1 - leg 2',
                6 => 'Section 3 - leg 3'
            ],
            $sut->fetchListOptions($this->context, false)
        );
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsEmpty()
    {
        $licenceId = 987;

        $sut = new LicenceDecisionLegislation();
        $sut->setData('licenceDecisionLegislation', []);

        // tests formatGroups is called to give the array structure below
        $this->assertEquals(
            [],
            $sut->fetchListOptions($this->context, false)
        );
    }

    /**
     * Test fetchUserListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->expectException(UnexpectedResponseException::class);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();
        $sut = new LicenceDecisionLegislation();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $sut->fetchListData($this->context);
    }
}
