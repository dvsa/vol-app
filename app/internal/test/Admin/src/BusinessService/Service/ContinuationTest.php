<?php

/**
 * Continuation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace AdminTest\BusinessService\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\BusinessService\Service\Continuation;
use OlcsTest\Bootstrap;
use Common\BusinessService\Response;
use Common\Service\Entity\ContinuationDetailEntityService;

/**
 * Continuation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = new Continuation();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testProcessNoLicences()
    {
        $params = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        // Mocks
        $mockLicence = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicence);

        // Expectations
        $mockLicence->shouldReceive('findForContinuationCriteria')
            ->with(['foo' => 'bar'])
            ->andReturn([]);

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_NO_OP, $response->getType());
    }

    public function testProcessWithLicencesWithException()
    {
        $params = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $stubbedLicences = [
            [

            ]
        ];

        // Mocks
        $mockLicence = m::mock();
        $mockContinuation = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicence);
        $this->sm->setService('Entity\Continuation', $mockContinuation);

        // Expectations
        $mockLicence->shouldReceive('findForContinuationCriteria')
            ->with(['foo' => 'bar'])
            ->andReturn($stubbedLicences);

        $mockContinuation->shouldReceive('save')
            ->with(['foo' => 'bar'])
            ->andThrow('\Exception', 'Failed');

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals('Failed to create continuation record, please try again', $response->getMessage());
    }

    public function testProcessWithLicencesWithoutId()
    {
        $params = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $stubbedLicences = [
            [

            ]
        ];

        // Mocks
        $mockLicence = m::mock();
        $mockContinuation = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicence);
        $this->sm->setService('Entity\Continuation', $mockContinuation);

        // Expectations
        $mockLicence->shouldReceive('findForContinuationCriteria')
            ->with(['foo' => 'bar'])
            ->andReturn($stubbedLicences);

        $mockContinuation->shouldReceive('save')
            ->with(['foo' => 'bar'])
            ->andReturn([]);

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals('Failed to create continuation record, please try again', $response->getMessage());
    }

    public function testProcessWithLicencesWithDetailFail()
    {
        $params = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $stubbedLicences = [
            [
                'id' => 222
            ]
        ];

        $expectedRecords = [
            [
                'licence' => 222,
                'received' => 'N',
                'status' => ContinuationDetailEntityService::STATUS_PREPARED,
                'continuation' => 111
            ]
        ];

        // Mocks
        $mockLicence = m::mock();
        $mockContinuation = m::mock();
        $mockContinuationDetail = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicence);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationDetail);

        // Expectations
        $mockLicence->shouldReceive('findForContinuationCriteria')
            ->with(['foo' => 'bar'])
            ->andReturn($stubbedLicences);

        $mockContinuation->shouldReceive('save')
            ->with(['foo' => 'bar'])
            ->andReturn(['id' => 111]);

        $mockContinuationDetail->shouldReceive('createRecords')
            ->with($expectedRecords)
            ->andThrow('\Exception', 'Failed');

        $mockContinuation->shouldReceive('delete')
            ->with(111);

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals(
            'Failed to create one or more continuation detail records, please try again',
            $response->getMessage()
        );
    }

    public function testProcessSuccess()
    {
        $params = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $stubbedLicences = [
            [
                'id' => 222
            ],
            [
                'id' => 333
            ]
        ];

        $expectedRecords = [
            [
                'licence' => 222,
                'received' => 'N',
                'status' => ContinuationDetailEntityService::STATUS_PREPARED,
                'continuation' => 111
            ],
            [
                'licence' => 333,
                'received' => 'N',
                'status' => ContinuationDetailEntityService::STATUS_PREPARED,
                'continuation' => 111
            ]
        ];

        // Mocks
        $mockLicence = m::mock();
        $mockContinuation = m::mock();
        $mockContinuationDetail = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicence);
        $this->sm->setService('Entity\Continuation', $mockContinuation);
        $this->sm->setService('Entity\ContinuationDetail', $mockContinuationDetail);

        // Expectations
        $mockLicence->shouldReceive('findForContinuationCriteria')
            ->with(['foo' => 'bar'])
            ->andReturn($stubbedLicences);

        $mockContinuation->shouldReceive('save')
            ->with(['foo' => 'bar'])
            ->andReturn(['id' => 111]);

        $mockContinuationDetail->shouldReceive('createRecords')
            ->with($expectedRecords);

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals(['id' => 111], $response->getData());
    }
}
