<?php

/**
 * Withdraw Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Withdraw Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class WithdrawControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\WithdrawController');
        $this->markTestSkipped();
    }

    public function testIndexActionGet()
    {
        $id = 69;
        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockWithdrawForm();

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('internal-application-withdraw-title', $view->getVariable('title'));

        $this->assertSame($mockForm, $view->getVariable('form'));
    }


    /**
     * @group application_controller
     */
    public function testIndexActionPostCancelButton()
    {
        $id = 69;
        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->mockWithdrawForm();

        $this->setPost(['form-actions' => ['cancel' => 'foo']]);

        $this->mockService('Helper\FlashMessenger', 'addWarningMessage');

        $redirect = m::mock();
        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application', ['application' => $id])
            ->andReturn($redirect);

        $this->assertSame($redirect, $this->sut->indexAction());
    }

    public function testIndexActionWithPostConfirm()
    {
        $id = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockWithdrawForm();

        $postData = [
            'form-actions' => [
                'submit' => ''
            ],
            'withdraw-details' => [
                'reason' => 'reg_in_error',
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

        $this->mockService('Processing\Application', 'processWithdrawApplication')
            ->with($id, 'reg_in_error')
            ->once();

        $this->mockService('Helper\Translation', 'translateReplace')
            ->with('application-withdrawn-successfully', [$id])
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

    public function testIndexActionWithPostInvalid()
    {
        $id = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $mockForm = $this->mockWithdrawForm();

        $postData = [
            'form-actions' => [
                'submit' => ''
            ],
            'withdraw-details' => [
                'reason' => '',
            ],
        ];
        $this->setPost($postData);

        $mockForm
            ->shouldReceive('setData')
                ->with($postData)
                ->once()
                ->andReturnSelf()
            ->shouldReceive('isValid')
                ->once()
                ->andReturn(false);

        $this->mockService('Processing\Application', 'processWithdrawApplication')
            ->never();

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('internal-application-withdraw-title', $view->getVariable('title'));

        $this->assertSame($mockForm, $view->getVariable('form'));
    }

    protected function mockWithdrawForm()
    {
        $mockForm = $this->createMockForm('Withdraw');
        $mockForm->shouldReceive('get->get->setLabel')->with('Confirm');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);

        return $mockForm;
    }
}
