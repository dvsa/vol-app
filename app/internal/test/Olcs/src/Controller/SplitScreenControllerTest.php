<?php

/**
 * Split Screen Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller;

use Common\Service\Script\ScriptFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\SplitScreenController;

/**
 * Split Screen Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SplitScreenControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->mockScriptFactory = m::mock(ScriptFactory::class)->makePartial();
        $this->sut = new SplitScreenController($this->mockScriptFactory);
    }

    public function testIndexAction()
    {

        // Expectations
        $this->mockScriptFactory->shouldReceive('loadFile')
            ->with('split-screen');

        $view = $this->sut->indexAction();

        $this->assertEquals('layout/split-screen', $view->getTemplate());
        $this->assertEquals(true, $view->terminate());
    }
}
