<?php

declare(strict_types=1);

namespace OlcsTest;

use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;
use Olcs\Controller\ConsultantRegistrationController;
use Mockery as m;

class ConsultantRegistrationControllerTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(ConsultantRegistrationController::class)
            ->makePartial();
    }


    public function testContactYourAdministratorAction(): void
    {
        // Arrange
        $expectedViewName = 'olcs/user-registration/contact-your-administrato';
        $viewModel = new ViewModel();
        $viewModel->setTemplate($expectedViewName);

        $this->sut->shouldReceive('contactYourAdministratorAction')
            ->andReturn($viewModel);
        $result = $this->sut->contactYourAdministratorAction();
        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals($expectedViewName, $result->getTemplate());
        $this->assertEmpty($result->getVariable('pageTitle'));
    }
}
