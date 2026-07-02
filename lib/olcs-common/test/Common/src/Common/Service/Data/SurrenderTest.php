<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Surrender;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence as Qry;
use Mockery as m;

class SurrenderTest extends AbstractDataServiceTestCase
{
    /** @var Surrender */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new Surrender($this->abstractDataServiceServices);
    }

    public function testFetchSurrender(): void
    {
        $params = ['id' => 7];
        $expected = [];
        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
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
            ->andReturn($expected)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($expected, $this->sut->fetchSurrenderData(7));
    }

    public function testThrowsExceptionIfNot200Response(): void
    {
        $this->expectException(DataServiceException::class);

        $params = ['id' => 7];
        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['id'], $dto->getId());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);
        $this->sut->fetchSurrenderData(7);
    }
}
