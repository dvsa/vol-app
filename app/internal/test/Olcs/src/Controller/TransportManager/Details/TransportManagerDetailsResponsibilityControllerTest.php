<?php

/**
 * Transport manager details responsibilities controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Entity\TransportManageApplicationEntityService;
use Common\Service\Entity\TransportManageLicenceEntityService;
use Zend\View\Model\ViewModel;

/**
 * Transport manager details responsibilities controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsResponsibilityControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    /**
     * Set up action
     */
    public function setUpAction()
    {
        $this->sut =
            m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test index action
     * 
     * @group tmResponsibility
     */
    public function testIndexAction()
    {
        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-responsibility')
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.applications', 'applications')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('applicationsTable')
                ->getMock()
            )
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.licences', 'licences')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('licencesTable')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(['applicationsTable' => 'applicationsTable', 'licencesTable' => 'licencesTable'])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $applicationStatus = [
            'apsts_consideration',
            'apsts_not_submitted',
            'apsts_granted'
        ];
        $mockTransportManagerApplication = m::mock()
            ->shouldReceive('getTransportManagerApplications')
            ->with(1, $applicationStatus)
            ->andReturn('applications')
            ->getMock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTransportManagerApplication);

        $licenceStatus = [
            'lsts_valid',
            'lsts_suspended',
            'lsts_curtailed'
        ];
        $mockTransportManagerLicence = m::mock()
            ->shouldReceive('getTransportManagerLicences')
            ->with(1, $licenceStatus)
            ->andReturn('licences')
            ->getMock();

        $this->sm->setService('Entity\TransportManagerLicence', $mockTransportManagerLicence);

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }
}
