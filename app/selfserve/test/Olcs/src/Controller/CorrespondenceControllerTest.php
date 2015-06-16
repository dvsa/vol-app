<?php

/**
 * CorrespondenceControllerTest.php
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees as OutstandingFeesQry;

/**
 * Class CorrespondenceControllerTest
 *
 * @package OlcsTest\Controller
 */
class CorrespondenceControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->markTestSkipped();

        $this->sut = m::mock('\Olcs\Controller\CorrespondenceController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        $organisationId = 1;

        $correspondence = array(
            'Count' => 3,
            'Results' => array(
                array(
                    'id' => '1',
                    'licence' => array(),
                    'createdOn' => '2015-01-01',
                    'accessed' => 'N'
                ),
                array(
                    'id' => '2',
                    'licence' => array(),
                    'createdOn' => '2014-01-01',
                    'accessed' => 'N'
                ),
                array(
                    'id' => '3',
                    'licence' => array(),
                    'createdOn' => '2013-01-01',
                    'accessed' => 'Y'
                )
            )
        );

        $fees = [
            [
                'id' => 1,
                'description' => 'fee 1',
                'licence' => [
                    'id' => 7,
                    'licNo' => 'LIC7',
                ],
            ],
            [
                'id' => 2,
                'description' => 'fee 2',
                'licence' => [
                    'id' => 8,
                    'licNo' => 'LIC8',
                ],
            ],
            [
                'id' => 3,
                'description' => 'fee 3',
                'licence' => [
                    'id' => 9,
                    'licNo' => 'LIC9',
                ],
            ],
        ];

        // Organisation
        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn($organisationId);

        // Correspondence inbox.
        $mockCorrespondenceInbox = m::mock()
            ->shouldReceive('getCorrespondenceByOrganisation')
            ->with($organisationId)
            ->andReturn($correspondence)
            ->getMock();
        $this->sm->setService('Entity\CorrespondenceInbox', $mockCorrespondenceInbox);

        // Fees
        $mockFeesResponse = m::mock();
        $this->sut
            ->shouldReceive('handleQuery')
            ->with(m::type(OutstandingFeesQry::class))
            ->andReturn($mockFeesResponse);

        $mockFeesResponse
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn(['outstandingFees' => $fees]);

        // Navigation
        $mockNavigation = m::mock();
        $this->sm->setService('Olcs\Navigation\DashboardNavigation', $mockNavigation);

        // Table data
        $tableData = array_map(
            function ($correspondence) {
                return array(
                    'id' => $correspondence['id'],
                    'correspondence' => $correspondence,
                    'licence' => $correspondence['licence'],
                    'date' => $correspondence['createdOn']
                );
            },
            $correspondence['Results']
        );

        // Correspondence table.
        $mockTable = m::mock()
            ->shouldReceive('buildTable')
            ->with(
                'correspondence',
                $tableData
            )
            ->andReturn($correspondence)
            ->getMock();
        $this->sm->setService('Table', $mockTable);

        // Navigation
        $mockNavigation
            ->shouldReceive('findOneById')
            ->with('dashboard-fees')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 3)
                    ->getMock()
            )
            ->shouldReceive('findOneById')
            ->with('dashboard-correspondence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 2)
                    ->getMock()
            );

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('correspondence', $view->getTemplate());
    }

    public function testCorrespondenceAction()
    {
        $correspondenceId = 1;

        $correspondence = array(
            'document' => array(
                'identifier' => 1,
                'filename' => 'filename'
            )
        );

        $this->sut->shouldReceive('params->fromRoute')
            ->with('correspondenceId', null)
            ->andReturn('1');

        // Correspondence inbox.
        $mockCorrespondenceInbox = m::mock()
            ->shouldReceive('getById')
            ->with($correspondenceId)
            ->andReturn($correspondence)
            ->getMock();
        $this->sm->setService('Entity\CorrespondenceInbox', $mockCorrespondenceInbox);

        // Business service
        $this->sm->setService(
            'BusinessServiceManager',
            m::mock()
                ->shouldReceive('get')
                ->with('Lva\AccessCorrespondence')
                ->andReturn(
                    m::mock()
                        ->shouldReceive('process')
                        ->with($correspondence)
                        ->getMock()
                )
                ->getMock()
        );

        $this->sut->shouldReceive('redirect->toRoute')
            ->with(
                'getfile',
                array(
                    'file' => $correspondence['document']['identifier'],
                    'name' => $correspondence['document']['filename']
                )
            );

        $this->sut->accessCorrespondenceAction();
    }
}
