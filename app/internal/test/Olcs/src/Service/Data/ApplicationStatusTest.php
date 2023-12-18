<?php

namespace OlcsTest\Service\Data;

use Common\Exception\DataServiceException;
use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\ApplicationStatus;
use Dvsa\Olcs\Transfer\Query\DataService\ApplicationStatus as Qry;
use Laminas\Http\Response;

/**
 * @covers \Olcs\Service\Data\ApplicationStatus
 */
class ApplicationStatusTest extends AbstractListDataServiceTestCase
{
    const ORG_ID = 9999;

    /** @var ApplicationStatus */
    private $sut;

    /** @var  \Laminas\Http\Response | m\MockInterface */
    private $mockResp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ApplicationStatus($this->abstractListDataServiceServices);

        $this->mockResp = m::mock(Response::class);
    }

    public function testSetters()
    {
        $this->sut->setOrgId('unit_Org');

        static::assertEquals('unit_Org', $this->sut->getOrgId());
    }

    public function testFetchListData()
    {
        $results = ['results' => ['unit_Results']];

        $this->transferAnnotationBuilder
            ->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $qry) {
                    static::assertEquals(self::ORG_ID, $qry->getOrganisation());

                    return $this->query;
                }
            );

        $this->mockResp
            ->shouldReceive('isOk')->once()->andReturn(true)
            ->shouldReceive('getResult')->once()->andReturn($results);

        $this->mockHandleQuery($this->mockResp);

        $this->sut->setOrgId(self::ORG_ID);

        static::assertEquals($results['results'], $this->sut->fetchListData());
        static::assertEquals($results['results'], $this->sut->fetchListData()); //ensure data is cached
    }

    public function testFetchListDataWithException()
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $this->mockResp->shouldReceive('isOk')->once()->andReturn(false);

        $this->mockHandleQuery($this->mockResp);

        $this->sut->fetchListData([]);
    }
}
