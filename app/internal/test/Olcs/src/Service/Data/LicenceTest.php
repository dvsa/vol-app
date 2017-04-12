<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Licence;
use Mockery as m;
use Dvsa\Olcs\Transfer\Query\Licence\GetList as GetLicenceListQry;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Licence data service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceTest extends AbstractDataServiceTestCase
{
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
        $dto = GetLicenceListQry::create($params);
        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturnUsing(
                function ($dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['excludeStatuses'], []);
                    $this->assertEquals($params['organisation'], 1);
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

        $sut = new Licence();
        $sut->setOrganisationId(1);
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals($results['results'], $sut->fetchLicenceListData());
    }

    /**
     * Test fetchLicenceListData
     */
    public function testFetchLicenceListDataResponseNotOk()
    {
        $mockTransferAnnotationBuilder = m::mock()->shouldReceive('createQuery')->once()->andReturn('query')->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sut = new Licence();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->mockServiceLocator->shouldReceive('get')
            ->with('Helper\FlashMessenger')
            ->andReturn(
                m::mock()
                ->shouldReceive('addErrorMessage')
                ->with('unknown-error')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals([], $sut->fetchLicenceListData());
    }

    public function testFetchListOption()
    {
        $mockTransferAnnotationBuilder = m::mock()->shouldReceive('createQuery')->once()->andReturn('query')->getMock();
        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn(['results' => [['licNo' => 'AB123', 'id' => 123]]])
            ->twice()
            ->getMock();

        $sut = new Licence();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals([123 => 'AB123'], $sut->fetchListOptions('context'));
    }

    public function testFetchListOptionNoResults()
    {
        $mockTransferAnnotationBuilder = m::mock()->shouldReceive('createQuery')->once()->andReturn('query')->getMock();
        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn(['results' => 'foo'])
            ->twice()
            ->getMock();

        $sut = new Licence();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse);

        $this->assertEquals([], $sut->fetchListOptions('context'));
    }
}
