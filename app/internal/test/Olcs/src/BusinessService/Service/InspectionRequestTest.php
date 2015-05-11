<?php

/**
 * Inspection Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Common\BusinessService\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;
use Common\Service\Entity\InspectionRequestEntityService;

/**
 * Inspection Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestTest extends MockeryTestCase
{
    /**
     * Test process method
     *
     * @group inspectionRequestServiceTest
     */
    public function testProcessUpdate()
    {
        $sut = m::mock('Olcs\BusinessService\Service\InspectionRequest')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $sm = Bootstrap::getServiceManager();
        $sut->setServiceLocator($sm);

        $applicationId = 1;
        $licenceId = 2;
        $requestorUser = 3;
        $result = ['id' => 10];

        $data = [
            'data' => ['bar' => 'foo', 'id' => 10],
            'type' => 'application',
            'applicationId' => $applicationId,
            'licenceId' => $licenceId,
            'requestorUser' => $requestorUser
        ];
        $dataToSave = [
            'bar' => 'foo',
            'licence' => $licenceId,
            'application' => $applicationId,
            'requestorUser' => $requestorUser,
            'id' => 10
        ];
        $sm->setService(
            'Entity\User',
            m::mock()
            ->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn(['id' => $requestorUser])
            ->getMock()
        );

        $sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('save')
            ->with($dataToSave)
            ->once()
            ->andReturn($result)
            ->getMock()
        );

        $response = $sut->process($data);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals($result, $response->getData());
    }

    /**
     * Test process method
     *
     * @group inspectionRequestServiceTest
     */
    public function testProcessCallingFromGrant()
    {
        $sut = m::mock('Olcs\BusinessService\Service\InspectionRequest')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $sm = Bootstrap::getServiceManager();
        $sut->setServiceLocator($sm);

        $applicationId = 1;
        $licenceId = 2;
        $requestorUser = 3;
        $result = ['id' => 10];

        $data = [
            'data' => ['inspection-request-grant-details' => ['dueDate' => 3, 'caseworkerNotes' => 'notes']],
            'type' => 'applicationFromGrant',
            'applicationId' => $applicationId,
            'requestorUser' => $requestorUser
        ];
        $dataToSave = [
            'requestType' => InspectionRequestEntityService::REQUEST_TYPE_NEW_OP,
            'requestDate' => '2015-01-01',
            'dueDate' => '2015-04-01',
            'resultType' => InspectionRequestEntityService::RESULT_TYPE_NEW,
            'requestorNotes' =>  'notes',
            'reportType' => InspectionRequestEntityService::REPORT_TYPE_MAINTENANCE_REQUEST,
            'operatingCentre' => 1,
            'application' => $applicationId,
            'licence' => $licenceId,
            'requestorUser' => $requestorUser
        ];

        $sm->setService(
            'Helper\Date',
            m::mock()
            ->shouldReceive('getDateObject')
            ->andReturn(
                m::mock()
                ->shouldReceive('format')
                ->with('Y-m-d')
                ->andReturn('2015-01-01')
                ->shouldReceive('add')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('format')
                    ->with('Y-m-d')
                    ->andReturn('2015-04-01')
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock()
        );

        $sm->setService(
            'Olcs\Service\Data\OperatingCentresForInspectionRequest',
            m::mock()
            ->shouldReceive('setType')
            ->with('application')
            ->shouldReceive('fetchListOptions')
            ->with('')
            ->andReturn([1 => ['foo']])
            ->shouldReceive('setIdentifier')
            ->with($applicationId)
            ->once()
            ->getMock()
        );

        $sm->setService(
            'Entity\Application',
            m::mock()
            ->shouldReceive('getLicenceIdForApplication')
            ->with($applicationId)
            ->andReturn($licenceId)
            ->getMock()
        );

        $sm->setService(
            'Entity\User',
            m::mock()
            ->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn(['id' => $requestorUser])
            ->getMock()
        );

        $sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('save')
            ->with($dataToSave)
            ->once()
            ->andReturn($result)
            ->getMock()
        );

        $sm->setService(
            'Email\InspectionRequest',
            m::mock()
            ->shouldReceive('sendInspectionRequestEmail')
            ->with(m::type('Olcs\View\Model\Email\InspectionRequest'), 10)
            ->once()
            ->andReturn(true)
            ->getMock()
        );

        $response = $sut->process($data);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals($result, $response->getData());
    }

    /**
     * Test process method with new request and email failure
     *
     * @group inspectionRequestServiceTest
     */
    public function testProcessNewWithEmailFailure()
    {
        $this->markTestIncomplete(
            'Need to remove this when email service will be implemented and'
            . 'Inspection Request Business Service will be fixed'
        );
        $sut = m::mock('Olcs\BusinessService\Service\InspectionRequest')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $sm = Bootstrap::getServiceManager();
        $sut->setServiceLocator($sm);

        $applicationId = 1;
        $licenceId = 2;
        $requestorUser = 3;
        $result = ['id' => 10];

        $data = [
            'data' => ['bar' => 'foo'],
            'type' => 'application',
            'applicationId' => $applicationId,
            'licenceId' => $licenceId,
            'requestorUser' => $requestorUser
        ];
        $dataToSave = [
            'bar' => 'foo',
            'licence' => $licenceId,
            'application' => $applicationId,
            'requestorUser' => $requestorUser,
        ];
        $sm->setService(
            'Entity\User',
            m::mock()
            ->shouldReceive('getCurrentUser')
            ->andReturn(['id' => $requestorUser])
            ->getMock()
        );

        $entityService = m::mock();
        $sm->setService('Entity\InspectionRequest', $entityService);

        $entityService
            ->shouldReceive('save')
            ->with($dataToSave)
            ->andReturn($result)
            ->getMock();

        $sm->setService(
            'Email\InspectionRequest',
            m::mock()
            ->shouldReceive('sendInspectionRequestEmail')
            ->with(m::type('Olcs\View\Model\Email\InspectionRequest'), 10)
            ->once()
            ->andReturn(false)
            ->getMock()
        );

        $entityService
            ->shouldReceive('delete')
            ->with(10)
            ->once();

        $response = $sut->process($data);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals([], $response->getData());
    }
}
