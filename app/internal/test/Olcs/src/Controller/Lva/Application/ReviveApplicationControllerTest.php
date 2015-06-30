<?php


namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Class ReviveApplicationControllerTest
 *
 * Tests for reviving an application.
 *
 * @package OlcsTest\Controller\Lva\Application
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class ReviveApplicationControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\ReviveApplicationController');
        $this->markTestSkipped();
    }

    public function testIndexActionGet()
    {
        $id = 69;
        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockReviveApplicationForm();

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('internal-application-revive-application-title', $view->getVariable('title'));

        $this->assertSame($mockForm, $view->getVariable('form'));
    }

    public function testIndexActionWithPostConfirm()
    {
        $id = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockReviveApplicationForm();

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

        $this->mockService('Processing\Application', 'processReviveApplication')
            ->with($id)
            ->once();

        $this->mockService('Helper\Translation', 'translateReplace')
            ->with('application-revive-application-successfully', [$id])
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

    protected function mockReviveApplicationForm()
    {
        $mockForm = $this->createMockForm('GenericConfirmation');
        $mockForm->shouldReceive('get->get->setValue')->with('internal-application-revive-application-confirm');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);

        return $mockForm;
    }
}
