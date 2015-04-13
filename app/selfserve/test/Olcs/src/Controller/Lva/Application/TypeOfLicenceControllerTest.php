<?php

namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;

/**
 * Test Type Of Licence Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TypeOfLicenceControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\TypeOfLicenceController');
    }

    /**
     * Test custom render method
     */
    public function testRenderCreateSetsDefaultStepNumber()
    {
        $form = m::mock('Zend\Form\Form');
        $view = $this->sut->renderCreateApplication('my_page', $form);
        $this->assertInstanceOf('Common\View\Model\Section', $view);

        $vars = $view->getVariables();

        $this->assertEquals('1', $vars['stepX']);
    }
}
