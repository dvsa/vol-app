<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;
use Laminas\Form\Form;
use Common\View\Model\Section;

class TypeOfLicenceControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockController(TypeOfLicenceController::class);
    }

    /**
     * Test custom render method
     */
    public function testRenderCreateSetsDefaultStepNumber(): void
    {
        $form = m::mock(Form::class);
        $view = $this->sut->renderCreateApplication('my_page', $form);
        $this->assertInstanceOf(Section::class, $view);

        $vars = $view->getVariables();

        $this->assertEquals('1', $vars['stepX']);
    }
}
