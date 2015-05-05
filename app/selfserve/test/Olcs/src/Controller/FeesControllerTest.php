<?php

/**
 * Fees Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Fees Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\FeesController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        $fees = [
            'Count' => 2,
            'Results' => [
                [
                    'id' => 1,
                    'description' => 'fee1',
                    'licence' => [
                        'id' => 7,
                        'licNo' => 'LIC7',
                    ],
                ],
                [
                    'id' => 2,
                    'description' => 'fee2',
                    'licence' => [
                        'id' => 8,
                        'licNo' => 'LIC8',
                    ],
                ],
            ],
        ];

        $organisationId = 99;

        // mocks
        $mockNavigation = m::mock();
        $this->sm->setService('Olcs\Navigation\DashboardNavigation', $mockNavigation);

        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);

        $mockTableService = m::mock();
        $this->sm->setService('Table', $mockTableService);

        $mockTable = m::mock();

        // expectations
        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with($organisationId)
            ->once()
            ->andReturn($fees);

        $mockNavigation
            ->shouldReceive('findOneById')
            ->with('dashboard-fees')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 2)
                    ->getMock()
            )
            ->shouldReceive('findOneById')
            ->with('dashboard-correspondence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 0)
                    ->getMock()
            );

        $mockTableService
            ->shouldReceive('buildTable')
            ->once()
            ->with(
                'fees',
                [
                    [
                        'id' => 1,
                        'description' => 'fee1',
                        'licNo' => 'LIC7',
                    ],
                    [
                        'id' => 2,
                        'description' => 'fee2',
                        'licNo' => 'LIC8',
                    ],
                ]
            )
            ->andReturn($mockTable);

        $view = $this->sut->indexAction();

        // assertions
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('fees', $view->getTemplate());
        // $this->assertEquals(['apps'], $view->getVariable('applications'));
        // $this->assertEquals(['vars'], $view->getVariable('variations'));
        // $this->assertEquals(['lics'], $view->getVariable('licences'));
    }
}
