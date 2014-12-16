<?php

/**
 * Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewControllerTest extends MockeryTestCase
{
    protected $sm;

    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Licence\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group licence-overview-controller
     */
    public function testIndexAction()
    {
        $licenceId = 4;
        $stubbedLicenceData = [
            'licNo' => 'xxx',
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => [
                'id' => 'yyy'
            ],
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            ]
        ];
        $stubbedSections = [];

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId)
            ->shouldReceive('getAccessibleSections')
            ->andReturn($stubbedSections);

        $mockLicenceEntity = m::mock();
        $mockLicenceEntity->shouldReceive('getOverview')
            ->with($licenceId)
            ->andReturn($stubbedLicenceData);

        $this->sm->setService('Entity\Licence', $mockLicenceEntity);

        $response = $this->sut->indexAction();

        $expectedVariables = [
            'shouldShowCreateVariation' => true,
            'licenceId' => 'xxx',
            'startDate' => '2014-01-01',
            'renewalDate' => '2015-01-01',
            'status' => 'yyy',
            'sections' => []
        ];

        $this->assertEquals($expectedVariables, $response->getVariables());
    }

    /**
     * @group licence-overview-controller
     */
    public function testIndexActionWithoutCreateVariation()
    {
        $licenceId = 4;
        $stubbedLicenceData = [
            'licNo' => 'xxx',
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => [
                'id' => 'yyy'
            ],
            'licenceType' => [
                'id' => LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED
            ]
        ];
        $stubbedSections = [];

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId)
            ->shouldReceive('getAccessibleSections')
            ->andReturn($stubbedSections);

        $mockLicenceEntity = m::mock();
        $mockLicenceEntity->shouldReceive('getOverview')
            ->with($licenceId)
            ->andReturn($stubbedLicenceData);

        $this->sm->setService('Entity\Licence', $mockLicenceEntity);

        $response = $this->sut->indexAction();

        $expectedVariables = [
            'shouldShowCreateVariation' => false,
            'licenceId' => 'xxx',
            'startDate' => '2014-01-01',
            'renewalDate' => '2015-01-01',
            'status' => 'yyy',
            'sections' => []
        ];

        $this->assertEquals($expectedVariables, $response->getVariables());
    }
}
