<?php

/**
 * Licence Processing Inspection Request controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Application\Processing;

use \Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;

/**
 * Licence Processing Inspection Request controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceProcessingInspectionRequestControllerTest extends MockeryTestCase
{
    /**
     * Set up action
     */
    public function setUpAction()
    {
        $this->markTestSkipped();
        $this->sut =
            m::mock('\Olcs\Controller\Licence\Processing\LicenceProcessingInspectionRequestController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     * 
     * @group licenceProcessingInspectionRequestController
     */
    public function testIndexAction()
    {
        $this->setUpAction();

        $params = [
            'page' => 1,
            'sort' => 'id',
            'order' => 'desc',
            'limit' => 10,
            'query' => 'query'
        ];

        $licenceId = 1;

        $this->sut
            ->shouldReceive('checkForCrudAction')
            ->with(null, [], 'id')
            ->once()
            ->shouldReceive('getQueryOrRouteParam')
            ->with('page', 1)
            ->andReturn($params['page'])
            ->once()
            ->shouldReceive('getQueryOrRouteParam')
            ->with('sort', 'id')
            ->andReturn($params['sort'])
            ->once()
            ->shouldReceive('getQueryOrRouteParam')
            ->with('order', 'desc')
            ->andReturn($params['order'])
            ->once()
            ->shouldReceive('getQueryOrRouteParam')
            ->with('limit', 10)
            ->andReturn($params['limit'])
            ->once()
            ->shouldReceive('fromRoute')
            ->with('licence')
            ->andReturn($licenceId)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('getQuery')
                ->andReturn($params['query'])
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getTable')
            ->with('inspectionRequest', 'results', $params)
            ->andReturn('table')
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('getInspectionRequestList')
            ->with($params, $licenceId)
            ->andReturn('results')
            ->getMock()
        );

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Test add action with POST and empty enforcement area
     * 
     * @group licenceProcessingInspectionRequestController
     */
    public function testAddActionWithPostAndEmptyEnforcementArea()
    {
        $this->setUpAction();

        $licenceId = 2;
        $inspectionRequestId = 3;

        $this->sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('licence')
            ->once()
            ->shouldReceive('setIdentifier')
            ->with($licenceId)
            ->once()
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
            ->shouldReceive('getEnforcementArea')
            ->with($licenceId)
            ->andReturn(['enforcementArea' => null])
            ->getMock()
        );

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('licence')
            ->andReturn($licenceId)
            ->shouldReceive('getForm')
            ->with('InspectionRequest')
            ->once()
            ->shouldReceive('getRequest')
            ->once()
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn($inspectionRequestId)
            ->shouldReceive('addErrorMessage')
            ->with('internal-inspection-request.area-not-set')
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['licence' => $licenceId])
                ->andReturn('redirect')
                ->getMock()
            );

        $this->assertEquals('redirect', $this->sut->addAction());
    }
}
