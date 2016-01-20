<?php

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\IndexController;
use OlcsTest\Bootstrap;

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexControllerTest extends MockeryTestCase
{
    /**
     * @var Admin\Controller\IndexController
     */
    protected $sut;

    public function setUp()
    {
        $this->markTestSkipped();
        $this->sut = new IndexController();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        // Mocks
        $mockVhm = m::mock();
        $this->sm->setService('viewHelperManager', $mockVhm);

        // Expectations
        $mockVhm->shouldReceive('get')
            ->with('placeholder')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContainer')
                ->once()
                ->with('navigationId')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('set')
                    ->once()
                    ->with('admin-dashboard')
                    ->getMock()
                )
                ->getMock()
            );

        // Assertions
        $response = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $response);
        $this->assertEquals('Admin', $response->getVariable('pageTitle'));
        $this->assertEquals('layout/base', $response->getTemplate());

        $header = $response->getChildrenByCaptureTo('header');
        $content = $response->getChildrenByCaptureTo('content');

        $this->assertCount(1, $header);
        $this->assertCount(2, $content);

        $this->assertEquals('partials/header', $header[0]->getTemplate());
        $this->assertEquals('placeholder', $content[0]->getTemplate());
        $this->assertEquals('layout/admin-layout', $content[1]->getTemplate());

        $this->assertSame($content[0], $content[1]->getChildren()[0]);
    }
}
