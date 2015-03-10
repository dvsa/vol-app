<?php

/**
 * Interim Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Data\FeeTypeDataService;
use Common\Service\Entity\FeeEntityService;

/**
 * Interim Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimControllerTest extends AbstractHttpControllerTestCase
{
    protected $mockForm;

    protected $mockApplicationService;

    protected $mockRequest;

    protected $interimData = [
        'operatingCentres' => 'operatingCentres',
        'licenceVehicles' => 'licenceVehicles',
        'id' => 1,
        'version' => 1,
        'interimReason' => 'reason',
        'interimStart' => '2014/01/01',
        'interimEnd' => '2015/01/01',
        'interimAuthVehicles' => 10,
        'interimAuthTrailers' => 20,
        'interimStatus' => ['id' => ApplicationEntityService::INTERIM_STATUS_REQUESTED],
        'licence' => ['id' => 1]
    ];

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Application\InterimController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);

    }

    /**
     * Test index action
     * 
     * @group interimController
     */
    public function testIndexAction()
    {
        $applicationId = 1;
        $this->mockGetForm($applicationId);

        $data = [
            'data' => $this->interimData,
            'requested' => [
                'interimRequested' => 'Y'
            ]
        ];

        $data['data']['interimStatus'] = $data['data']['interimStatus']['id'];
        unset($data['data']['operatingCentres']);
        unset($data['data']['licenceVehicles']);
        unset($data['data']['licence']);

        $this->mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($this->mockRequest)
            ->shouldReceive('render')
            ->andReturn('view');

        $this->mockForm
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $mockScript = m::mock()
            ->shouldReceive('loadFiles')
            ->with(['forms/interim'])
            ->getMock();
        $this->sm->setService('Script', $mockScript);

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Mock get form
     * 
     */
    protected function mockGetForm($applicationId)
    {
        $this->mockForm = m::mock()
            ->shouldReceive('get')
            ->with('operatingCentres')
            ->andReturn('operatingCentresElement')
            ->once()
            ->shouldReceive('get')
            ->with('vehicles')
            ->andReturn('vehiclesElement')
            ->once()
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('createForm')
            ->with('interim')
            ->andReturn($this->mockForm)
            ->shouldReceive('populateFormTable')
            ->with('operatingCentresElement', 'ocTable', 'operatingCentres')
            ->once()
            ->shouldReceive('populateFormTable')
            ->with('vehiclesElement', 'vehiclesTable', 'vehicles')
            ->once()
            ->getMock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->mockApplicationService = m::mock()
            ->shouldReceive('getDataForInterim')
            ->with($applicationId)
            ->andReturn($this->interimData)
            ->getMock();
        $this->sm->setService('Entity\Application', $this->mockApplicationService);

        $mockPluginManager = m::mock()
            ->shouldReceive('get')
            ->with('url')
            ->andReturn('url')
            ->getMock();

        $mockTable = m::mock()
            ->shouldReceive('buildTable')
            ->with('interim.operatingcentres', 'operatingCentres', ['url' => 'url'], false)
            ->andReturn('ocTable')
            ->once()
            ->shouldReceive('buildTable')
            ->with('interim.vehicles', 'licenceVehicles', ['url' => 'url'], false)
            ->andReturn('vehiclesTable')
            ->once()
            ->getMock();
        $this->sm->setService('Table', $mockTable);

        $this->sut
            ->shouldReceive('getIdentifier')
            ->andReturn($applicationId)
            ->shouldReceive('getPluginManager')
            ->andReturn($mockPluginManager)
            ->getMock();

        return $this->mockForm;
    }

    /**
     * Test index action with cancel button pressed
     * 
     * @group interimController
     */
    public function testIndexActionWithCancel()
    {
        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true);

        $this->mockRedirectToOverview();

        $this->assertInstanceOf('\Zend\Http\PhpEnvironment\Response', $this->sut->indexAction());
    }

    /**
     * Test index action set interim
     * 
     * @group interimController
     */
    public function testIndexActionSetInterim()
    {
        $applicationId = 1;
        $data = [
            'data' => $this->interimData,
            'requested' => [
                'interimRequested' => 'Y'
            ]
        ];

        $data['data']['interimStatus'] = $data['data']['interimStatus']['id'];
        unset($data['data']['operatingCentres']);
        unset($data['data']['licenceVehicles']);
        unset($data['data']['licence']);
        $data['operatingCentres'] = ['id' => [1, 2]];
        $data['licenceVehicles'] = ['id' => [1, 2]];

        $this->mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($data)
            ->getMock();

        $this->mockGetForm($applicationId);

        $this->mockApplicationService
            ->shouldReceive('saveInterimData')
            ->with($data, true)
            ->getMock();

        $this->mockForm
            ->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('getData')
            ->andReturn($data)
            ->shouldReceive('get')
            ->with('data')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('interimStatus')
                ->once()
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(ApplicationEntityService::INTERIM_STATUS_REQUESTED)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('requested')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('interimRequested')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn('Y')
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->mockRequest)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->once()
            ->shouldReceive('isButtonPressed')
            ->with('save')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->mockRedirectToOverview(true);

        $this->mockCreateInterimFee($applicationId);

        $this->assertInstanceOf('\Zend\Http\PhpEnvironment\Response', $this->sut->indexAction());
    }

    /**
     * Test index action to unset interim
     * 
     * @group interimController
     */
    public function testIndexActionUnsetInterim()
    {
        $applicationId = 1;
        $data = [
            'data' => $this->interimData,
            'requested' => [
                'interimRequested' => 'Y'
            ]
        ];

        $data['data']['interimStatus'] = $data['data']['interimStatus']['id'];
        unset($data['data']['operatingCentres']);
        unset($data['data']['licenceVehicles']);
        unset($data['data']['licence']);

        $dataToSave = [
            'data' => [
                'id' => 1,
                'version' => 1
            ]
        ];

        $this->mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($data)
            ->getMock();

        $this->mockGetForm($applicationId);

        $this->mockApplicationService
            ->shouldReceive('saveInterimData')
            ->with($dataToSave, false)
            ->getMock();

        $this->mockForm
            ->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('get')
            ->with('data')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('interimStatus')
                ->once()
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(ApplicationEntityService::INTERIM_STATUS_REQUESTED)
                    ->getMock()
                )
                ->shouldReceive('get')
                ->with('id')
                ->once()
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(1)
                    ->getMock()
                )
                ->shouldReceive('get')
                ->with('version')
                ->once()
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(1)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('requested')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('interimRequested')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn('')
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->mockRequest)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->once()
            ->shouldReceive('isButtonPressed')
            ->with('save')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->mockRedirectToOverview(true);

        $this->mockCancelInterimFee($applicationId);

        $this->assertInstanceOf('\Zend\Http\PhpEnvironment\Response', $this->sut->indexAction());
    }

    /**
     * Mock redirect to overview method
     * 
     * @param bool $success
     */
    public function mockRedirectToOverview($success = false)
    {
        $this->sut
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(1)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('\Zend\Http\PhpEnvironment\Response')
                ->shouldReceive('toRoute')
                ->with(
                    'lva-application',
                    [
                        'application' => 1,
                        'action' => ''
                    ]
                )
                ->andReturnSelf()
                ->getMock()
            )
            ->getMock();

        if ($success) {
            $this->sut
                ->shouldReceive('addSuccessMessage')
                ->with('internal.interim.interim_details_saved')
                ->getMock();
        }
    }

    /**
     * Test index action with invaid form
     * 
     * @group interimController
     */
    public function testIndexActionInvalidForm()
    {
        $this->mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn([])
            ->getMock();

        $this->mockForm = m::mock()
            ->shouldReceive('setData')
            ->with([])
            ->shouldReceive('get')
            ->with('data')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('interimStatus')
                ->once()
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn(ApplicationEntityService::INTERIM_STATUS_REQUESTED)
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('requested')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('interimRequested')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn('Y')
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->mockRequest)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->once()
            ->shouldReceive('isButtonPressed')
            ->with('save')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getForm')
            ->andReturn($this->mockForm)
            ->shouldReceive('render')
            ->andReturn('view')
            ->getMock();

        $mockScript = m::mock()
            ->shouldReceive('loadFiles')
            ->with(['forms/interim'])
            ->getMock();
        $this->sm->setService('Script', $mockScript);

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Mock create interim fee
     * 
     */
    public function mockCreateInterimFee($applicationId)
    {
        $this->sut
            ->shouldReceive('getIdentifier')
            ->andReturn($applicationId);

        $mockApplicationProcessingService = m::mock()
            ->shouldReceive('getFeeTypeForApplication')
            ->with($applicationId, FeeTypeDataService::FEE_TYPE_GRANTINT)
            ->andReturn(['id' => 181])
            ->shouldReceive('createFee')
            ->with($applicationId, 1, FeeTypeDataService::FEE_TYPE_GRANTINT)
            ->getMock();
        $this->sm->setService('Processing\Application', $mockApplicationProcessingService);

        $mockFeeService = m::mock()
            ->shouldReceive('getFeeByTypeStatusesAndApplicationId')
            ->with(
                181,
                [FeeEntityService::STATUS_OUTSTANDING, FeeEntityService::STATUS_WAIVE_RECOMMENDED],
                $applicationId
            )
            ->andReturn(null)
            ->getMock();
        $this->sm->setService('Entity\Fee', $mockFeeService);

    }

    /**
     * Mock cancel interim fee
     * 
     */
    public function mockCancelInterimFee($applicationId)
    {
        $this->sut
            ->shouldReceive('getIdentifier')
            ->andReturn($applicationId);

        $mockApplicationProcessingService = m::mock()
            ->shouldReceive('getFeeTypeForApplication')
            ->with($applicationId, FeeTypeDataService::FEE_TYPE_GRANTINT)
            ->andReturn(['id' => 181])
            ->getMock();
        $this->sm->setService('Processing\Application', $mockApplicationProcessingService);

        $mockFeeService = m::mock()
            ->shouldReceive('getFeeByTypeStatusesAndApplicationId')
            ->with(
                181,
                [FeeEntityService::STATUS_OUTSTANDING, FeeEntityService::STATUS_WAIVE_RECOMMENDED],
                $applicationId
            )
            ->andReturn([['id' => 1]])
            ->shouldReceive('cancelByIds')
            ->with([1])
            ->getMock();
        $this->sm->setService('Entity\Fee', $mockFeeService);
    }
}
