<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataServiceServices;
use Olcs\Service\Data\SlaException;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\SlaExceptionList as Qry;
use Dvsa\Olcs\Api\Entity\Pi\SlaException as SlaExceptionEntity;
use Mockery as m;

/**
 * Class SlaExceptionTest
 * @package OlcsTest\Service\Data
 */
class SlaExceptionTest extends AbstractDataServiceTestCase
{
    /** @var SlaException */
    private $sut;
    
    /** @var AbstractListDataServiceServices */
    private $abstractListDataServiceServices;

    private $sampleData = [
        [
            'id' => 1,
            'slaDescription' => 'Standard Processing',
            'slaExceptionDescription' => 'Complex Case Extension',
            'effectiveFrom' => '2024-01-01',
            'effectiveTo' => '2024-12-31'
        ],
        [
            'id' => 2, 
            'slaDescription' => 'Standard Processing',
            'slaExceptionDescription' => 'Holiday Period Extension',
            'effectiveFrom' => '2024-01-01',
            'effectiveTo' => null
        ],
        [
            'id' => 3,
            'slaDescription' => 'Fast Track Processing', 
            'slaExceptionDescription' => 'Emergency Exception',
            'effectiveFrom' => '2024-06-01',
            'effectiveTo' => '2024-12-31'
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractListDataServiceServices = new AbstractListDataServiceServices(
            $this->abstractDataServiceServices
        );
        
        $this->sut = new SlaException($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        $results = ['result' => $this->sampleData];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(function ($dto) {
                $this->assertEquals('slaDescription', $dto->getSort());
                $this->assertEquals('ASC', $dto->getOrder());
                return $this->query;
            });

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $result = $this->sut->fetchListData();
        
        $this->assertEquals($this->sampleData, $result);
    }

    public function testFetchListDataWithCaching()
    {
        // First call should hit the API
        $results = ['result' => $this->sampleData];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        // First call
        $result1 = $this->sut->fetchListData();
        
        // Second call should use cached data (no additional expectations needed)
        $result2 = $this->sut->fetchListData();
        
        $this->assertEquals($this->sampleData, $result1);
        $this->assertEquals($this->sampleData, $result2);
    }

    public function testFetchListDataApiFailure()
    {
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

        $this->expectException(DataServiceException::class);
        $this->expectExceptionMessage('unknown-error');

        $this->sut->fetchListData();
    }

    public function testFormatDataForGroups()
    {
        $expected = [
            'Standard Processing' => [
                'label' => 'Standard Processing',
                'options' => [
                    1 => 'Complex Case Extension',
                    2 => 'Holiday Period Extension'
                ]
            ],
            'Fast Track Processing' => [
                'label' => 'Fast Track Processing', 
                'options' => [
                    3 => 'Emergency Exception'
                ]
            ]
        ];

        $result = $this->sut->formatDataForGroups($this->sampleData);
        
        $this->assertEquals($expected, $result);
    }

    public function testFormatDataForGroupsWithEntities()
    {
        // Test with SLA Exception entity objects
        $entity1 = m::mock(SlaExceptionEntity::class);
        $entity1->shouldReceive('getId')->andReturn(1);
        $entity1->shouldReceive('getSlaDescription')->andReturn('Standard Processing');
        $entity1->shouldReceive('getSlaExceptionDescription')->andReturn('Complex Case Extension');

        $entity2 = m::mock(SlaExceptionEntity::class);
        $entity2->shouldReceive('getId')->andReturn(2);
        $entity2->shouldReceive('getSlaDescription')->andReturn('Standard Processing');
        $entity2->shouldReceive('getSlaExceptionDescription')->andReturn('Holiday Period Extension');

        $entityData = [$entity1, $entity2];

        $expected = [
            'Standard Processing' => [
                'label' => 'Standard Processing',
                'options' => [
                    1 => 'Complex Case Extension',
                    2 => 'Holiday Period Extension'
                ]
            ]
        ];

        $result = $this->sut->formatDataForGroups($entityData);
        
        $this->assertEquals($expected, $result);
    }

    public function testFetchListOptionsWithGroups()
    {
        $results = ['result' => $this->sampleData];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $expected = [
            'Standard Processing' => [
                'label' => 'Standard Processing',
                'options' => [
                    1 => 'Complex Case Extension',
                    2 => 'Holiday Period Extension'
                ]
            ],
            'Fast Track Processing' => [
                'label' => 'Fast Track Processing',
                'options' => [
                    3 => 'Emergency Exception'
                ]
            ]
        ];

        $result = $this->sut->fetchListOptions(null, true);
        
        $this->assertEquals($expected, $result);
    }

    public function testFetchListOptionsWithoutGroups()
    {
        $results = ['result' => $this->sampleData];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $expected = [
            1 => 'Standard Processing - Complex Case Extension',
            2 => 'Standard Processing - Holiday Period Extension', 
            3 => 'Fast Track Processing - Emergency Exception'
        ];

        $result = $this->sut->fetchListOptions(null, false);
        
        $this->assertEquals($expected, $result);
    }

    public function testFetchListOptionsEmpty()
    {
        $results = ['result' => []];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($results)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $result = $this->sut->fetchListOptions();
        
        $this->assertEquals([], $result);
    }

    public function testFormatData()
    {
        $expected = [
            1 => 'Standard Processing - Complex Case Extension',
            2 => 'Standard Processing - Holiday Period Extension',
            3 => 'Fast Track Processing - Emergency Exception'
        ];

        $result = $this->sut->formatData($this->sampleData);
        
        $this->assertEquals($expected, $result);
    }

    public function testFormatDataWithEntities()
    {
        $entity1 = m::mock(SlaExceptionEntity::class);
        $entity1->shouldReceive('getId')->andReturn(1);
        $entity1->shouldReceive('getSlaDescription')->andReturn('Standard Processing');
        $entity1->shouldReceive('getSlaExceptionDescription')->andReturn('Complex Case Extension');

        $entity2 = m::mock(SlaExceptionEntity::class);
        $entity2->shouldReceive('getId')->andReturn(2);
        $entity2->shouldReceive('getSlaDescription')->andReturn('Fast Track Processing');
        $entity2->shouldReceive('getSlaExceptionDescription')->andReturn('Emergency Exception');

        $entityData = [$entity1, $entity2];

        $expected = [
            1 => 'Standard Processing - Complex Case Extension',
            2 => 'Fast Track Processing - Emergency Exception'
        ];

        $result = $this->sut->formatData($entityData);
        
        $this->assertEquals($expected, $result);
    }
}
