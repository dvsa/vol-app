<?php

/**
 * Schedule41Test.php
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Common\Service\Entity\ConditionUndertakingEntityService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

use Common\BusinessService\Response;

use OlcsTest\Bootstrap;

use Olcs\BusinessService\Service\Lva\Schedule41;

/**
 * Class Schedule41Test
 *
 * Test the schedule 41 process logic.
 *
 * @package OlcsTest\BusinessService\Service\Lva
 */
class Schedule41Test extends MockeryTestCase
{
    protected $sut = null;

    protected $sm = null;

    public function setUp()
    {
        $this->sut = new Schedule41();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider testProcessDataProvider
     */
    public function testProcess($data, $response)
    {
        $mockS41 = m::mock()
            ->shouldReceive('save')
            ->once()
            ->with(
                array(
                    'application' => $data['winningApplication']['id'],
                    'licence' => $data['losingLicence']['id'],
                    'surrenderLicence' => $data['data']['surrenderLicence'],
                    'receivedDate' => '2015-01-01'
                )
            )->andReturn(
                array('id' => 1)
            );

        $mockDateHelper = m::mock()
            ->shouldReceive('getDate')
            ->once()
            ->andReturn('2015-01-01');

        $mockLicenceOperatingCentre = m::mock()
            ->shouldReceive('forceUpdate')
            ->times(3);

        $mockApplicationOperatingCentre = m::mock()
            ->shouldReceive('save')
            ->with(
                array(
                    'application' => 1,
                    'action' => 'A',
                    'adPlaced' => 'N',
                    'operatingCentre' => 1,
                    'noOfTrailersRequired' => 5,
                    'noOfVehiclesRequired' => 10,
                    's4' => 1,
                )
            );

        $mockConditionUndertakingCentre = m::mock()
            ->shouldReceive('save')
            ->with(
                array(
                    'application' => 1,
                    'operatingCentre' => 1,
                    'conditionType' => 'cdt_con',
                    'addedVia' => 'cav_app',
                    'action' => 'A',
                    'attachedTo' => 'cat_oc',
                    'isDraft' => 'Y',
                    'isFulfilled' => 'N',
                    's4' => 1,
                    'notes' => 'Notes',
                )
            );

        $this->sm->setService('Helper\Date', $mockDateHelper->getMock());
        $this->sm->setService('Entity\Schedule41', $mockS41->getMock());
        $this->sm->setService('Entity\LicenceOperatingCentre', $mockLicenceOperatingCentre->getMock());
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockApplicationOperatingCentre->getMock());
        $this->sm->setService('Entity\ConditionUndertaking', $mockConditionUndertakingCentre->getMock());

        $this->assertEquals($this->sut->process($data)->getType(), $response);
    }

    public function testProcessDataProvider()
    {
        return array(
            array(
                array(
                    'winningApplication' => array(
                        'id' => 1
                    ),
                    'losingLicence' => array(
                        'id' => 1,
                        'operatingCentres' => array(
                            array(
                                'operating_centre_id' => 1,
                                'noOfTrailersRequired' => 5,
                                'noOfVehiclesRequired' => 10,
                                'operatingCentre' => array(
                                    'id' => 1,
                                    'conditionUndertakings' => array(
                                        array(
                                            'id' => 1,
                                            'condition_type' => ConditionUndertakingEntityService::TYPE_CONDITION,
                                            'notes' => 'Notes'
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    'data' => array(
                        'surrenderLicence' => 'N',
                        'table' => array(
                            'id' => array(1,2,3)
                        )
                    )
                ),
                Response::TYPE_SUCCESS
            )
        );
    }
}
