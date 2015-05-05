<?php

/**
 * Application Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessService\Service\Lva\ApplicationOverview as Sut;
use Common\BusinessService\Response;
use OlcsTest\Bootstrap;

/**
 * Application Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverviewTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $brm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->brm = m::mock('\Common\BusinessRule\BusinessRuleManager')->makePartial();

        $this->sut = new Sut();

        $this->sut->setServiceLocator($this->sm);
        $this->sut->setBusinessRuleManager($this->brm);
    }

    public function testProcess()
    {
        $params = [
            'details' => [
                'id' => 69,
                'version' => 2,
                'leadTcArea' => 'B',
                'translateToWelsh' => 'Y',
                'receivedDate' => '2015-04-01',
                'targetCompletionDate' => '2015-06-30',
            ],
            'tracking' => [
                'id' => 3,
                'version' => 4,
                'addressesStatus' => 0,
                'businessDetailsStatus' => 1,
                'businessTypeStatus' => 2,
                'undertakingsStatus' => 3,
            ],
        ];

        $applicationSaveData = [
            'id' => 69,
            'version' => 2,
            'receivedDate' => '2015-04-01',
            'targetCompletionDate' => '2015-06-30',
        ];

        $trackingSaveData = [
            'id' => 3,
            'version' => 4,
            'addressesStatus' => 0,
            'businessDetailsStatus' => 1,
            'businessTypeStatus' => 2,
            'undertakingsStatus' => 3,
        ];

        $application = [
            'id' => 69,
            'licence' => [
                'id' => 77,
                'organisation' => [
                    'id' => 99,
                ],
            ],
        ];

        $organisationSaveData = [
            'leadTcArea' => 'B',
        ];

        $licenceSaveData = [
            'translateToWelsh' => 'Y'
        ];

        // Mocks
        $appRule = m::mock('\Common\BusinessRule\BusinessRuleInterface');
        $this->brm->setService('ApplicationOverview', $appRule);

        $mockApplication = m::mock();
        $mockTracking = m::mock();
        $mockOrganisation = m::mock();
        $mockLicence = m::mock();
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Entity\ApplicationTracking', $mockTracking);
        $this->sm->setService('Entity\Organisation', $mockOrganisation);
        $this->sm->setService('Entity\Licence', $mockLicence);

        // Expectations
        $appRule
            ->shouldReceive('filter')
            ->with($params['details'])
            ->once()
            ->andReturn($applicationSaveData);

        $mockApplication
            ->shouldReceive('getOverview')
            ->with(69)
            ->once()
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($applicationSaveData)
            ->once();

        $mockTracking
            ->shouldReceive('save')
            ->with($trackingSaveData)
            ->once();

        $mockOrganisation
            ->shouldReceive('forceUpdate')
            ->with(99, $organisationSaveData)
            ->once();

        $mockLicence
            ->shouldReceive('forceUpdate')
            ->with(77, $licenceSaveData)
            ->once();

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
    }
}
