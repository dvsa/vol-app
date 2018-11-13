<?php

namespace OlcsTest\Controller\Licence\Surrender;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Licence\Surrender\StartController;
use Zend\View\View;

class StartControllerTest extends MockeryTestCase
{



    protected $sut;


    public function setUp()
    {
        $this->sut = new StartController();
        $this->sut->
    }

    public function testIndexAction()
    {
        $actual = $this->sut->indexAction();
        $this->assertInstanceOf(View::class, $actual);
    }

    protected function getServiceManager()
    {
        // TODO: Implement getServiceManager() method.
    }
}
