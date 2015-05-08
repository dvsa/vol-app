<?php

/**
 * ErruAppliedPenaltyResponseTest
 */
namespace OlcsTest\BusinessService\Service\Cases\Penalty;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessService\Service\Cases\Penalty\ErruAppliedPenaltyResponse;
use Common\BusinessService\Response as BusinessServiceResponse;
use Zend\Http\Response as ZendResponse;
use Common\Service\Data\Generic as GenericDataService;

/**
 * ErruAppliedPenaltyResponseTest
 */
class ErruAppliedPenaltyResponseTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new ErruAppliedPenaltyResponse();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($erruResponseCode, $businessServiceResponseCode)
    {
        $caseId = 29;
        $userId = 10;
        $caseData = [
            'id' => $caseId
        ];

        $params = [
            'caseId' => $caseId,
            'user' => $userId
        ];

        $mockDataServiceManager = m::mock();

        $mockCaseDataService = m::mock(GenericDataService::class);
        $mockCaseDataService->shouldReceive('fetchOne')->andReturn($caseData);
        $mockCaseDataService->shouldReceive('save');

        $erruResponse = new ZendResponse();
        $erruResponse->setStatusCode($erruResponseCode);

        $mockNrService = m::mock('Olcs\Service\Nr\RestHelper');
        $mockNrService->shouldReceive('sendErruResponse')->andReturn($erruResponse);
        $this->sm->setService('Olcs\Service\Nr\RestHelper', $mockNrService);

        $mockDataServiceManager
            ->shouldReceive('get')
            ->with('Generic\Service\Data\Cases')
            ->andReturn($mockCaseDataService);
        $this->sm->setService('DataServiceManager', $mockDataServiceManager);

        $businessResponse = new BusinessServiceResponse();
        $businessResponse->setType($businessServiceResponseCode);

        $businessResponse = $this->sut->process($params);

        $this->assertEquals($businessServiceResponseCode, $businessResponse->getType());
    }

    public function processDataProvider()
    {
        return [
            [202,BusinessServiceResponse::TYPE_SUCCESS],
            [400,BusinessServiceResponse::TYPE_FAILED],
        ];
    }
}
