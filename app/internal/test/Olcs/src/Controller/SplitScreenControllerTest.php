<?php

/**
 * Split Screen Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\Controller\SplitScreenController;

/**
 * Split Screen Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SplitScreenControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new SplitScreenController();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        // Mocks
        $script = m::mock();
        $this->sm->setService('Script', $script);

        // Expectations
        $script->shouldReceive('loadFile')
            ->with('split-screen');

        $view = $this->sut->indexAction();

        $this->assertEquals('layout/split-screen', $view->getTemplate());
        $this->assertEquals(true, $view->terminate());
    }
}
