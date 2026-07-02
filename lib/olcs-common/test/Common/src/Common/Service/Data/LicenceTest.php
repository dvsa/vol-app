<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Licence;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Dvsa\Olcs\Transfer\Query\Licence\OperatingCentres as OcQry;

/**
 * Class LicenceTest
 * @package OlcsTest\Service\Data
 */
class LicenceTest extends AbstractDataServiceTestCase
{
    /** @var Licence */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Licence($this->abstractDataServiceServices);
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

    public function testFetchLicenceData(): void
    {
        $licence = [
            'id' => 78,
            'trafficArea' => [
                'id' => 'B',
                'isNi' => true
            ],
            'niFlag' => 'Y'
        ];

        $expected = [
            'id' => 78,
            'trafficArea' => [
                'id' => 'B',
                'isNi' => true
            ],
            'niFlag' => 'Y',
        ];

        $params = [
            'id' => 78
        ];
        $dto = LicenceQry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(LicenceQry::class))
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
            ->andReturn($licence)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchLicenceData(78));
    }

    public function testFetchLicenceDataWithoutId(): void
    {
        $this->assertEquals([], $this->sut->fetchLicenceData());
    }

    public function testFetchLicenceDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(LicenceQry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->fetchLicenceData(78);
    }

    public function testFetchOperatingCentreData(): void
    {
        $licence = [
            'id' => 78,
            'operatingCentres' => [
                'operatingCentre' => 'oc',
            ],
        ];

        $expected = [
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
            ->andReturn($licence)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchOperatingCentreData(78));
    }

    public function testFetchOperatingCentresDataWithException(): void
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
}
