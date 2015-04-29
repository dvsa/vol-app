<?php

/**
 * Licence Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessService\Service\Lva\LicenceOverview as Sut;
use Common\BusinessService\Response;
use OlcsTest\Bootstrap;

/**
 * Licence Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceOverviewTest extends MockeryTestCase
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
            'id' => 77,
            'version' => 2,
            'details' => [
                'leadTcArea' => 'B',
                'translateToWelsh' => 'Y',
                'continuationDate' => [
                    'day' => '01',
                    'month' => '02',
                    'year' => '2025',
                ],
                'reviewDate' => [
                    'day' => '03',
                    'month' => '04',
                    'year' => '2016',
                ],
            ]
        ];

        $licenceSaveData = [
            'id' => 77,
            'version' => 2,
            'expiryDate' => '2025-02-01',
            'reviewDate' => '2016-04-03',
        ];

        $organisation = [
            'id' => 99,
        ];

        $organisationSaveData = [
            'leadTcArea' => 'B',
        ];

        // Mocks
        $checkDateRule = m::mock('\Common\BusinessRule\BusinessRuleInterface');
        $this->brm->setService('CheckDate', $checkDateRule);

        $mockLicence = m::mock();
        $mockOrganisation = m::mock();
        $this->sm->setService('Entity\Licence', $mockLicence);
        $this->sm->setService('Entity\Organisation', $mockOrganisation);

        // Expectations
        $checkDateRule
            ->shouldReceive('validate')
            ->with(['day' => '01', 'month' => '02', 'year' => '2025'])
            ->once()
            ->andReturn('2025-02-01')
            ->shouldReceive('validate')
            ->with(['day' => '03', 'month' => '04', 'year' => '2016'])
            ->once()
            ->andReturn('2016-04-03');

        $mockLicence
            ->shouldReceive('getOrganisation')
            ->with(77)
            ->once()
            ->andReturn($organisation)
            ->shouldReceive('save')
            ->with($licenceSaveData)
            ->once()
            ->shouldReceive('forceUpdate')
            ->with(77, ['translateToWelsh' => 'Y'])
            ->once();

        $mockOrganisation
            ->shouldReceive('forceUpdate')
            ->with(99, $organisationSaveData)
            ->once();

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
    }

    public function testProcessNoOp()
    {
        $params = [
            'id' => 77,
            'version' => 2,
            'details' => [
                'continuationDate' => ['invalid'],
                'reviewDate' => ['invalid']
            ],
        ];

        $checkDateRule = m::mock('\Common\BusinessRule\BusinessRuleInterface');
        $this->brm->setService('CheckDate', $checkDateRule);

        $checkDateRule
            ->shouldReceive('validate')
            ->with(['invalid'])
            ->andReturn(null);

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals(Response::TYPE_NO_OP, $response->getType());
    }
}
