<?php

namespace OlcsTest\Service\Data;

use Common\Service\Helper\FlashMessengerHelperService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Olcs\Service\Data\Licence;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Licence\GetList as Qry;

/**
 * Licence data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceTest extends AbstractDataServiceTestCase
{
    /** @var Licence */
    private $sut;

    /** @var FlashMessengerHelperService */
    protected $flashMessengerHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashMessengerHelper = m::mock(FlashMessengerHelperService::class);

        $this->sut = new Licence(
            $this->abstractDataServiceServices,
            $this->flashMessengerHelper
        );
    }

    /**
     * Test fetchLicenceListData
     */
    public function testFetchLicenceListData()
    {
        $results = ['results' => 'results'];
        $params = [
            'sort' => Licence::DEFAULT_SORT,
            'order' => Licence::DEFAULT_ORDER,
            'organisation' => 1,
            'excludeStatuses' => [],
        ];
        $dto = Qry::create($params);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['excludeStatuses'], []);
                    $this->assertEquals($params['organisation'], 1);
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

        $this->sut->setOrganisationId(1);
        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchLicenceListData());
    }

    /**
     * Test fetchLicenceListData
     */
    public function testFetchLicenceListDataResponseNotOk()
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

        $this->flashMessengerHelper->shouldReceive('addErrorMessage')
            ->with('unknown-error')
            ->once();

        $this->assertEquals([], $this->sut->fetchLicenceListData());
    }

    public function testFetchListOption()
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn(['results' => [['licNo' => 'AB123', 'id' => 123]]])
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals([123 => 'AB123'], $this->sut->fetchListOptions('context'));
    }

    public function testFetchListOptionNoResults()
    {
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn(['results' => 'foo'])
            ->twice()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals([], $this->sut->fetchListOptions('context'));
    }
}
