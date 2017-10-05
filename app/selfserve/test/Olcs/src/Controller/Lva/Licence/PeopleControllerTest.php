<?php


namespace OlcsTest\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractPeopleController;
use Mockery as m;
use Olcs\Controller\Lva\Licence\PeopleController;



class PeopleControllerTest extends m\Adapter\Phpunit\MockeryTestCase
{
    private $peopleController;

    /**
     *
     */
    public function setUp()
    {
        $this->peopleController = new PeopleController(); 
        
    }

    public function testInstance()
    {
        $this->assertInstanceOf(AbstractPeopleController::class,$this->peopleController);
    }


}