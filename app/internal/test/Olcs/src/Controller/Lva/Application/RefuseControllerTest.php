<?php

/**
 * Refuse Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Refuse Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RefuseControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\RefuseController');
        $this->markTestSkipped();
    }

    public function testIndexActionGet()
    {
        $id = 69;
        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockRefuseForm();

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('internal-application-refuse-title', $view->getVariable('title'));

        $this->assertSame($mockForm, $view->getVariable('form'));
    }

    public function testIndexActionWithPostConfirm()
    {
        $id = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockRefuseForm();

        $postData = [
            'form-actions' => [
                'submit' => ''
            ],
        ];
        $this->setPost($postData);

        $mockForm
            ->shouldReceive('setData')
                ->with($postData)
                ->once()
                ->andReturnSelf()
            ->shouldReceive('getData')
                ->andReturn($postData)
            ->shouldReceive('isValid')
                ->once()
                ->andReturn(true);

        $this->mockService('Processing\Application', 'processRefuseApplication')
            ->with($id)
            ->once();

        $this->mockService('Helper\Translation', 'translateReplace')
            ->with('application-refused-successfully', [$id])
            ->once()
            ->andReturn('SUCCESS MESSAGE');

        $this->mockService('Helper\FlashMessenger', 'addSuccessMessage')
            ->with('SUCCESS MESSAGE')
            ->once();

        $redirect = m::mock();
        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/overview', ['application' => $id])
            ->andReturn($redirect);

        $this->assertSame($redirect, $this->sut->indexAction());
    }

    protected function mockRefuseForm()
    {
        $mockForm = $this->createMockForm('GenericConfirmation');
        $mockForm->shouldReceive('get->get->setValue')->with('internal-application-refuse-confirm');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);

        return $mockForm;
    }
}
