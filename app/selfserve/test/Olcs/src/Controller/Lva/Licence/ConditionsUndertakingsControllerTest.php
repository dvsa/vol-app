<?php

/**
 * External Licence Conditions Undertakings Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Licence\ConditionsUndertakingsController;

/**
 * External Licence Conditions Undertakings Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new ConditionsUndertakingsController();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        $this->markTestSkipped();

        // Data
        $stubbedData = ['foo' => 'bar'];
        $stubbedConfig = ['bar' => 'foo'];

        // Mocks
        $mockGuidance = m::mock();
        $mockParams = m::mock('\Zend\Mvc\Controller\Plugin\Params');
        $mockLicence = m::mock();
        $mockLicenceReview = m::mock();

        $this->sm->setService('Helper\Guidance', $mockGuidance);
        $this->sm->setService('Entity\Licence', $mockLicence);
        $this->sm->setService('Review\LicenceConditionsUndertakings', $mockLicenceReview);

        $mockPm = m::mock('\Zend\Mvc\Controller\PluginManager')->makePartial();
        $mockPm->setService('params', $mockParams);
        $this->sut->setPluginManager($mockPm);

        // Expectations
        $mockGuidance->shouldReceive('append')
            ->with('cannot-change-conditions-undertakings-guidance');

        $mockParams->shouldReceive('setController')
            ->shouldReceive('__invoke')
            ->with('licence')
            ->andReturn(111);

        $mockLicence->shouldReceive('getConditionsAndUndertakings')
            ->with(111)
            ->andReturn($stubbedData);

        $mockLicenceReview->shouldReceive('getConfigFromData')
            ->with($stubbedData)
            ->andReturn($stubbedConfig);

        $view = $this->sut->indexAction();

        // layout -> licence-page -> read-only content

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('layout/layout', $view->getTemplate());
        $layoutContents = $view->getChildren();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $layoutContents[0]);
        $this->assertEquals('pages/licence-page', $layoutContents[0]->getTemplate());
        $pageContents = $layoutContents[0]->getChildren();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $pageContents[0]);
        $this->assertEquals('partials/read-only/subSections', $pageContents[0]->getTemplate());
        $this->assertEquals($stubbedConfig, $pageContents[0]->getVariables());
    }
}
