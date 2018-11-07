<?php
namespace OlcsTest\Controller\Licence\Surrender;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Licence\Surrender\StartController;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;

class StartControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;


    protected $sut;


    public function setUp()
    {
        $this->sut = new StartController();
        
    }

    public function testIndexAction()
    {

    }

    protected function getServiceManager()
    {
        // TODO: Implement getServiceManager() method.
    }
}
