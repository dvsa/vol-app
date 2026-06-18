<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Application;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as OcQry;
use Common\RefData as CommonRefData;
use Mockery as m;

/**
 * Class Application Test
 * @package CommonTest\Service
 */
class ApplicationTest extends AbstractDataServiceTestCase
{
    /** @var Application */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Application($this->abstractDataServiceServices);
    }

    public function testSetId(): void
    {
        $this->sut->setId(78);
        $this->assertEquals(78, $this->sut->getId());
    }

    public function testGetId(): void
    {
        $this->assertNull($this->sut->getId());
    }

    public function testFetchData(): void
    {
        $id = 1;
        $data = ['id' => 99];
        $this->sut->setData(1, $data);

        $result = $this->sut->fetchData($id);

        $this->assertEquals($data, $result);
    }

    /**
     * Test canHaveCases
     *
     * @dataProvider canHaveCasesDataProvider
     * @param array $application
     * @param int $expectedResult
     */
    public function testCanHaveCases($application, $expectedResult): void
    {
        $this->sut->setData($application['id'], $application);
        $this->assertEquals($expectedResult, $this->sut->canHaveCases($application['id']));
    }

    public function testFetchOperatingCentreData(): void
    {
        $application = [
            'id' => 78,
            'operatingCentres' => [
                'operatingCentre' => 'oc',
            ],
        ];

        $params = [
            'id' => 78
        ];
        $dto = OcQry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(OcQry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['id'], $dto->getId());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($application)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($application, $this->sut->fetchOperatingCentreData(78));
    }

    public function testFetchOperatingCentreDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(OcQry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchOperatingCentreData(78);
    }

    /**
     * Data provider for canHaveCases.
     *
     * @return array
     */
    public function canHaveCasesDataProvider()
    {
        return [
            // status / licence not set
            [
                ['id' => 100],
                false
            ],
            // licence without licNo
            [
                [
                    'id' => 100,
                    'licence' => ['licNo' => null]
                ],
                false
            ],
            // status NOT_SUBMITTED
            [
                [
                    'id' => 100,
                    'status' => ['id' => CommonRefData::APPLICATION_STATUS_NOT_SUBMITTED]
                ],
                false
            ],
            // licence with licNo and status different than NOT_SUBMITTED
            [
                [
                    'id' => 100,
                    'status' => ['id' => CommonRefData::APPLICATION_STATUS_GRANTED],
                    'licence' => ['licNo' => 'ABC']
                ],
                true
            ],
        ];
    }

    public function testFetchApplicationData(): void
    {
        $application = ['foo' => 'bar'];

        $params = [
            'id' => 78
        ];
        $dto = ApplicationQry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(ApplicationQry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['id'], $dto->getId());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($application)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($application, $this->sut->fetchApplicationData(78));
    }

    public function testFetchApplicationDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(ApplicationQry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchApplicationData(78);
    }
}
