<?php

/**
 * Transport Managers Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersControllerTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('\Olcs\Controller\Lva\Application\TransportManagersController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testDetailsAction()
    {
        /* @var $view \Zend\View\Model\ViewModel */
        $view = $this->sut->detailsAction();
        $this->assertEquals('pages/placeholder', $view->getChildren()[0]->getTemplate());
    }

    public function testAwaitingSignatureAction()
    {
        /* @var $view \Zend\View\Model\ViewModel */
        $view = $this->sut->awaitingSignatureAction();
        $this->assertEquals('pages/placeholder', $view->getChildren()[0]->getTemplate());
    }

    public function testTmSignedAction()
    {
        /* @var $view \Zend\View\Model\ViewModel */
        $view = $this->sut->tmSignedAction();
        $this->assertEquals('pages/placeholder', $view->getChildren()[0]->getTemplate());
    }

    public function testOperatorSignedAction()
    {
        /* @var $view \Zend\View\Model\ViewModel */
        $view = $this->sut->operatorSignedAction();
        $this->assertEquals('pages/placeholder', $view->getChildren()[0]->getTemplate());
    }

    public function testPostalApplicationAction()
    {
        /* @var $view \Zend\View\Model\ViewModel */
        $view = $this->sut->postalApplicationAction();
        $this->assertEquals('pages/placeholder', $view->getChildren()[0]->getTemplate());
    }
}
