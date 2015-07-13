<?php

/**
 * Application Processing Inspection Request controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Application\Processing;

use \Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Entity\InspectionRequestEntityService;
use Common\BusinessService\Response;

/**
 * Application Processing Inspection Request controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationProcessingInspectionRequestControllerTest extends MockeryTestCase
{
    /**
     * Set up action
     */
    public function setUpAction()
    {
        $this->markTestSkipped();
        $this->sut =
            m::mock('\Olcs\Controller\Application\Processing\ApplicationProcessingInspectionRequestController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     *
     * @group applicationProcessingInspectionRequestController
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
        $applicationId = 2;

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
            ->with('application')
            ->andReturn($applicationId)
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

        $this->sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Test edit action
     *
     * @group applicationProcessingInspectionRequestController
     */
    public function testEditAction()
    {
        $this->setUpAction();

        $applicationId = 1;
        $licenceId = 2;
        $inspectionRequestId = 3;

        $inspectionRequest = [
            'id' => 1,
            'version' => 2,
            'reportType' => ['id' => 3],
            'operatingCentre' => ['id' => 4],
            'inspectorName' => 'inspname',
            'requestType' => ['id' => 5],
            'requestDate' => '2014-01-01',
            'dueDate' => '2015-01-01',
            'returnDate' => '2016-01-01',
            'resultType' => ['id' => 6],
            'fromDate' => '2014-01-01',
            'toDate' => '2015-01-01',
            'vehiclesExaminedNo' => 10,
            'trailersExaminedNo' => 20,
            'requestorNotes' => 'rnotes',
            'inspectorNotes' => 'inotes'
        ];
        $formData = [
            'id' => $inspectionRequest['id'],
            'version' => $inspectionRequest['version'],
            'reportType' => $inspectionRequest['reportType']['id'],
            'operatingCentre' => $inspectionRequest['operatingCentre']['id'],
            'inspectorName' => $inspectionRequest['inspectorName'],
            'requestType' => $inspectionRequest['requestType']['id'],
            'requestDate' => $inspectionRequest['requestDate'],
            'dueDate' => $inspectionRequest['dueDate'],
            'returnDate' => $inspectionRequest['returnDate'],
            'resultType' => $inspectionRequest['resultType']['id'],
            'fromDate' => $inspectionRequest['fromDate'],
            'toDate' => $inspectionRequest['toDate'],
            'vehiclesExaminedNo' => $inspectionRequest['vehiclesExaminedNo'],
            'trailersExaminedNo' => $inspectionRequest['trailersExaminedNo'],
            'requestorNotes' => $inspectionRequest['requestorNotes'],
            'inspectorNotes' => $inspectionRequest['inspectorNotes']
        ];

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
            ->shouldReceive('getEnforcementArea')
            ->with($licenceId)
            ->andReturn(
                [
                    'enforcementArea' => ['name' => 'enforcementarea']
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('getInspectionRequest')
            ->with($inspectionRequestId)
            ->andReturn($inspectionRequest)
            ->getMock()
        );

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with(['data' => $formData])
            ->getMock();

        $this->sm->setService(
            'translator',
            m::mock()
            ->shouldReceive('translate')
            ->with('internal-application-processing-inspection-request-edit')
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $this->sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('application')
            ->shouldReceive('setIdentifier')
            ->with($applicationId)
            ->getMock()
        );

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('application')
            ->andReturn($applicationId)
            ->shouldReceive('getForm')
            ->with('InspectionRequest')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn($inspectionRequestId)
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->editAction());
    }

    /**
     * Test add action
     *
     * @group applicationProcessingInspectionRequestController
     */
    public function testAddAction()
    {
        $this->setUpAction();

        $applicationId = 1;
        $licenceId = 2;
        $inspectionRequestId = 3;

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
            ->shouldReceive('getEnforcementArea')
            ->with($licenceId)
            ->andReturn(
                [
                    'enforcementArea' => ['name' => 'enforcementarea']
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Helper\Date',
            m::mock()
            ->shouldReceive('getDate')
            ->andReturn('2015-01-01')
            ->getMock()
        );

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('data')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('reportType')
                ->andReturn(
                    m::mock()
                   ->shouldReceive('setValue')
                   ->with(InspectionRequestEntityService::REPORT_TYPE_MAINTENANCE_REQUEST)
                   ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('requestDate')
                ->andReturn(
                    m::mock()
                   ->shouldReceive('setValue')
                   ->with('2015-01-01')
                   ->getMock()
                )
                ->once()
                ->shouldReceive('get')
                ->with('resultType')
                ->andReturn(
                    m::mock()
                   ->shouldReceive('setValue')
                   ->with(InspectionRequestEntityService::RESULT_TYPE_NEW)
                   ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->getMock();

        $this->sm->setService(
            'translator',
            m::mock()
            ->shouldReceive('translate')
            ->with('internal-application-processing-inspection-request-add')
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $this->sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('application')
            ->shouldReceive('setIdentifier')
            ->with($applicationId)
            ->getMock()
        );

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('application')
            ->andReturn($applicationId)
            ->shouldReceive('getForm')
            ->with('InspectionRequest')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn($inspectionRequestId)
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->addAction());
    }

    /**
     * Test edit action with POST
     *
     * @group applicationProcessingInspectionRequestController
     */
    public function testEditActionWithPost()
    {
        $this->setUpAction();

        $applicationId = 1;
        $licenceId = 2;
        $inspectionRequestId = 3;

        $inspectionRequest = [
            'id' => 1,
            'version' => 2,
            'reportType' => ['id' => 3],
            'operatingCentre' => ['id' => 4],
            'inspectorName' => 'inspname',
            'requestType' => ['id' => 5],
            'requestDate' => '2014-01-01',
            'dueDate' => '2015-01-01',
            'returnDate' => '2016-01-01',
            'resultType' => ['id' => 6],
            'fromDate' => '2014-01-01',
            'toDate' => '2015-01-01',
            'vehiclesExaminedNo' => 10,
            'trailersExaminedNo' => 20,
            'requestorNotes' => 'rnotes',
            'inspectorNotes' => 'inotes'
        ];
        $formData = [
            'id' => $inspectionRequest['id'],
            'version' => $inspectionRequest['version'],
            'reportType' => $inspectionRequest['reportType']['id'],
            'operatingCentre' => $inspectionRequest['operatingCentre']['id'],
            'inspectorName' => $inspectionRequest['inspectorName'],
            'requestType' => $inspectionRequest['requestType']['id'],
            'requestDate' => $inspectionRequest['requestDate'],
            'dueDate' => $inspectionRequest['dueDate'],
            'returnDate' => $inspectionRequest['returnDate'],
            'resultType' => $inspectionRequest['resultType']['id'],
            'fromDate' => $inspectionRequest['fromDate'],
            'toDate' => $inspectionRequest['toDate'],
            'vehiclesExaminedNo' => $inspectionRequest['vehiclesExaminedNo'],
            'trailersExaminedNo' => $inspectionRequest['trailersExaminedNo'],
            'requestorNotes' => $inspectionRequest['requestorNotes'],
            'inspectorNotes' => $inspectionRequest['inspectorNotes']
        ];
        $post = [
            'data' => $formData
        ];

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
            ->shouldReceive('getEnforcementArea')
            ->with($licenceId)
            ->andReturn(
                [
                    'enforcementArea' => ['name' => 'enforcementarea']
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('getInspectionRequest')
            ->with($inspectionRequestId)
            ->andReturn($inspectionRequest)
            ->getMock()
        );

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->getMock()
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $this->sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $this->sm->setService(
            'BusinessServiceManager',
            m::mock()
            ->shouldReceive('get')
            ->with('InspectionRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->with(
                    [
                        'data' => $post['data'],
                        'licenceId' => $licenceId,
                        'applicationId' => $applicationId,
                        'type' => 'application'
                    ]
                )
                ->andReturn(new Response(Response::TYPE_SUCCESS))
                ->getMock()
            )
            ->getMock()
        );

        $this->sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('application')
            ->shouldReceive('setIdentifier')
            ->with($applicationId)
            ->getMock()
        );

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('application')
            ->andReturn($applicationId)
            ->shouldReceive('getForm')
            ->with('InspectionRequest')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn($inspectionRequestId)
            ->shouldReceive('addSuccessMessage')
            ->with('internal-inspection-request-inspection-request-updated')
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['application' => $applicationId])
                ->andReturn('redirect')
                ->getMock()
            );

        $this->assertEquals('redirect', $this->sut->editAction());
    }

    /**
     * Test add action with POST and empty enforcement area
     *
     * @group applicationProcessingInspectionRequestController
     */
    public function testAddActionWithPostAndEmptyEnforcementArea()
    {
        $this->setUpAction();

        $applicationId = 1;
        $licenceId = 2;
        $inspectionRequestId = 3;

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
            ->shouldReceive('getEnforcementArea')
            ->with($licenceId)
            ->andReturn(['enforcementArea' => null])
            ->getMock()
        );

        $this->sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $this->sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('application')
            ->shouldReceive('setIdentifier')
            ->with($applicationId)
            ->getMock()
        );

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('application')
            ->andReturn($applicationId)
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
                ->with(null, ['application' => $applicationId])
                ->andReturn('redirect')
                ->getMock()
            );

        $this->assertEquals('redirect', $this->sut->addAction());
    }

    /**
     * Test edit action with POST form not valid
     *
     * @group applicationProcessingInspectionRequestController1
     */
    public function testEditActionWithPostFormNotValid()
    {
        $this->setUpAction();

        $applicationId = 1;
        $licenceId = 2;
        $inspectionRequestId = 3;

        $inspectionRequest = [
            'id' => 1,
            'version' => 2,
            'reportType' => ['id' => 3],
            'operatingCentre' => ['id' => 4],
            'inspectorName' => 'inspname',
            'requestType' => ['id' => 5],
            'requestDate' => '2014-01-01',
            'dueDate' => '2015-01-01',
            'returnDate' => '2016-01-01',
            'resultType' => ['id' => 6],
            'fromDate' => '2014-01-01',
            'toDate' => '2015-01-01',
            'vehiclesExaminedNo' => 10,
            'trailersExaminedNo' => 20,
            'requestorNotes' => 'rnotes',
            'inspectorNotes' => 'inotes'
        ];
        $formData = [
            'id' => $inspectionRequest['id'],
            'version' => $inspectionRequest['version'],
            'reportType' => $inspectionRequest['reportType']['id'],
            'operatingCentre' => $inspectionRequest['operatingCentre']['id'],
            'inspectorName' => $inspectionRequest['inspectorName'],
            'requestType' => $inspectionRequest['requestType']['id'],
            'requestDate' => $inspectionRequest['requestDate'],
            'dueDate' => $inspectionRequest['dueDate'],
            'returnDate' => $inspectionRequest['returnDate'],
            'resultType' => $inspectionRequest['resultType']['id'],
            'fromDate' => $inspectionRequest['fromDate'],
            'toDate' => $inspectionRequest['toDate'],
            'vehiclesExaminedNo' => $inspectionRequest['vehiclesExaminedNo'],
            'trailersExaminedNo' => $inspectionRequest['trailersExaminedNo'],
            'requestorNotes' => $inspectionRequest['requestorNotes'],
            'inspectorNotes' => $inspectionRequest['inspectorNotes']
        ];
        $post = [
            'data' => $formData
        ];

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
            ->shouldReceive('getEnforcementArea')
            ->with($licenceId)
            ->andReturn(
                [
                    'enforcementArea' => ['name' => 'enforcementarea']
                ]
            )
            ->getMock()
        );

        $this->sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('getInspectionRequest')
            ->with($inspectionRequestId)
            ->andReturn($inspectionRequest)
            ->getMock()
        );

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($post)
            ->getMock()
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->getMock();

        $this->sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $this->sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('application')
            ->shouldReceive('setIdentifier')
            ->with($applicationId)
            ->getMock()
        );

        $this->sut
            ->shouldReceive('fromRoute')
            ->with('application')
            ->andReturn($applicationId)
            ->shouldReceive('getForm')
            ->with('InspectionRequest')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('fromRoute')
            ->with('id')
            ->andReturn($inspectionRequestId)
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->sm->setService(
            'translator',
            m::mock()
            ->shouldReceive('translate')
            ->with('internal-application-processing-inspection-request-edit')
            ->getMock()
        );

        $this->assertEquals('view', $this->sut->editAction());
    }
}
