<?php

/**
 * LicenceDecisionLegislation data service test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Licence as LicenceDataService;
use Olcs\Service\Data\LicenceDecisionLegislation;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Decision\DecisionList as Qry;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;

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

    /** @var LicenceDecisionLegislation */
    private $sut;

    /** @var LicenceDataService */
    protected $licenceDataService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->licenceDataService = m::mock(LicenceDataService::class);

        $this->sut = new LicenceDecisionLegislation(
            $this->abstractDataServiceServices,
            $this->licenceDataService
        );
    }

    /**
     * Test fetchListData
     */
    public function testFetchListData()
    {
        $results = ['results' => 'results'];

        $dto = Qry::create($this->context);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) {
                    $this->assertEquals('sectionCode', $dto->getSort());
                    $this->assertEquals('ASC', $dto->getOrder());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData($this->context));

        // test cached results
        $this->assertEquals($results['results'], $this->sut->fetchListData($this->context));
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsUsingGroups()
    {
        $this->sut->setData('licenceDecisionLegislation', $this->listData);

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
            $this->sut->fetchListOptions($this->context, true)
        );
    }

    /**
     * Test fetchListOptions
     */
    public function testFetchListOptionsWithoutGroups()
    {
        $this->sut->setData('licenceDecisionLegislation', $this->listData);

        // tests formatGroups is called to give the array structure below
        $this->assertEquals(
            [
                5 => 'Section 1 - leg 1',
                9 => 'Section 1 - leg 2',
                6 => 'Section 3 - leg 3'
            ],
            $this->sut->fetchListOptions($this->context, false)
        );
    }

    /**
     * Test fetchListOptionsEmpty
     */
    public function testFetchListOptionsEmpty()
    {
        $this->sut->setData('licenceDecisionLegislation', []);

        // tests formatGroups is called to give the array structure below
        $this->assertEquals(
            [],
            $this->sut->fetchListOptions($this->context, false)
        );
    }

    /**
     * Test fetchUserListData with exception
     */
    public function testFetchListDataWithException()
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchListData($this->context);
    }
}
