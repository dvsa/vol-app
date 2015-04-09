<?php

/**
 * Inspector Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Common\BusinessService\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;

/**
 * Inspector Request Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InspectionRequestTest extends MockeryTestCase
{
    /**
     * Test process method
     *
     * @group inspectionRequestServiveTest
     */
    public function testProcess()
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
            'requestorUser' => $requestorUser
        ];
        $sm->setService(
            'Entity\User',
            m::mock()
            ->shouldReceive('getCurrentUser')
            ->andReturn(['id' => $requestorUser])
            ->getMock()
        );

        $sm->setService(
            'Entity\InspectionRequest',
            m::mock()
            ->shouldReceive('save')
            ->with($dataToSave)
            ->andReturn($result)
            ->getMock()
        );

        $response = $sut->process($data);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals($result, $response->getData());
    }
}
