<?php

/**
 * Inspector Request Update Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Common\BusinessService\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;
use Olcs\BusinessService\Service\InspectionRequestUpdate;
use Common\Service\Entity\InspectionRequestEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Exception\ResourceNotFoundException;

/**
 * Inspector Request Update Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InspectionRequestUpdateTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new InspectionRequestUpdate();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test process method
     *
     * @dataProvider successProvider
     */
    public function testProcessSuccess($id, $status, $expectedResultType, $translateKey, $expectedTaskDescription)
    {
        $inspectionRequest = [
            'id' => $id,
            'resultType' => [
                'id' => InspectionRequestEntityService::RESULT_TYPE_NEW,
            ],
            'licence' => ['id' => 7],
            'application' => ['id' => 8],
        ];

        // mocks
        $mockEntityService = m::mock();
        $this->sm->setService('Entity\InspectionRequest', $mockEntityService);
        $taskProcessingMock = m::mock();
        $this->sm->setService('Processing\Task', $taskProcessingMock);
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $taskBusinessServiceMock = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm->setService('Task', $taskBusinessServiceMock);
        $this->sut->setBusinessServiceManager($bsm);
        $translator = m::mock();
        $this->sm->setService('Helper\Translation', $translator);

        // expectations
        $mockEntityService
            ->shouldReceive('getInspectionRequest')
            ->once()
            ->with($id)
            ->andReturn($inspectionRequest)
            ->shouldReceive('forceUpdate')
            ->once()
            ->with($id, ['resultType' => $expectedResultType]);

        $taskProcessingMock
            ->shouldReceive('getAssignment')
            ->once()
            ->with(
                [
                    'category' => CategoryDataService::CATEGORY_LICENSING,
                    'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_INSPECTION_REQUEST_SEMINAR,
                ]
            )
            ->andReturn(
                [
                    'assignedToTeam' => 9,
                    'assignedToUser' => 10,
                ]
            );

        $translator
            ->shouldReceive('translateReplace')
            ->with($translateKey, [$id])
            ->once()
            ->andReturn($expectedTaskDescription);

        $expectedTaskData = [
            'category'       => CategoryDataService::CATEGORY_LICENSING,
            'subCategory'    => CategoryDataService::TASK_SUB_CATEGORY_INSPECTION_REQUEST_SEMINAR,
            'description'    => $expectedTaskDescription,
            'isClosed'       => 'N',
            'urgent'         => 'N',
            'licence' => 7,
            'application' => 8,
            'assignedToTeam' => 9,
            'assignedToUser' => 10,
        ];
        $taskBusinessServiceMock
            ->shouldReceive('process')
            ->once()
            ->with($expectedTaskData)
            ->andReturn(new Response(Response::TYPE_SUCCESS));

        $params = [
            'id' => $id,
            'status' => $status,
        ];
        $response = $this->sut->process($params);

        $this->assertInstanceOf('Common\BusinessService\Response', $response);
        $this->assertTrue($response->isOk());
    }

    public function successProvider()
    {
        return [
            'satisfactory' => [
                123,
                'S',
                'insp_res_t_new_sat', // InspectionRequestEntityService::RESULT_TYPE_SATISFACTORY,
                'inspection-request-task-description-satisfactory',
                'Satisfactory inspection request: ID 123',
            ],
            'unsatisfactory' => [
                123,
                'U',
                'insp_res_t_new_unsat', // InspectionRequestEntityService::RESULT_TYPE_UNSATISFACTORY
                'inspection-request-task-description-unsatisfactory',
                'Unsatisfactory inspection request: ID 123',
            ],
        ];
    }

    /**
     * Test process method when inspection request not found
     */
    public function testProcessNotFound()
    {
        $id = 123;
        $status = 'S';

        // mocks
        $mockEntityService = m::mock();
        $this->sm->setService('Entity\InspectionRequest', $mockEntityService);

        // expectations
        $mockEntityService
            ->shouldReceive('getInspectionRequest')
            ->once()
            ->with($id)
            ->andThrow(new ResourceNotFoundException());

        $params = [
            'id' => $id,
            'status' => $status,
        ];
        $response = $this->sut->process($params);

        $this->assertInstanceOf('Common\BusinessService\Response', $response);
        $this->assertFalse($response->isOk());
        $this->assertEquals(Response::TYPE_NOT_FOUND, $response->getType());
    }

    public function testProcessInvalidStatusCode()
    {
        $id = 123;
        $status = 'foo';

        $params = [
            'id' => $id,
            'status' => $status,
        ];
        $response = $this->sut->process($params);

        $this->assertInstanceOf('Common\BusinessService\Response', $response);
        $this->assertFalse($response->isOk());
    }


    /**
     * Test process method when inspection request status isn't changing
     */
    public function testProcessNoOp()
    {
        $id = 123;
        $status = 'S';

        $inspectionRequest = [
            'id' => $id,
            'resultType' => [
                'id' => InspectionRequestEntityService::RESULT_TYPE_SATISFACTORY,
            ],
        ];

        // mocks
        $mockEntityService = m::mock();
        $this->sm->setService('Entity\InspectionRequest', $mockEntityService);

        // expectations
        $mockEntityService
            ->shouldReceive('getInspectionRequest')
            ->once()
            ->with($id)
            ->andReturn($inspectionRequest);

        $params = [
            'id' => $id,
            'status' => $status,
        ];
        $response = $this->sut->process($params);

        $this->assertInstanceOf('Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_NO_OP, $response->getType());
        $this->assertTrue($response->isOk());
    }
}
