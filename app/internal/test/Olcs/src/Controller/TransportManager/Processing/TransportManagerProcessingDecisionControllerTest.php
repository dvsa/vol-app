<?php
/**
 * Transport manager task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\TransportManager\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Transport manager task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerProcessingDecisionControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('Olcs\Controller\TransportManager\Processing\TransportManagerProcessingDecisionController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);

        parent::setUp();
    }

    public function testCanRemoveActionWithErrors()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn(1);
        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturn(
                m::mock()->shouldReceive('getResult')
                    ->andReturn(
                        [
                            'isDetached' => true,
                            'hasUsers' => false
                        ]
                    )->getMock()
            );

        $this->sut->shouldReceive('redirectToRoute')->with(
            'transport-manager/remove',
            [
                'transportManager' => 1
            ]
        )->andReturn('REDIRECT');

        $this->assertEquals($this->sut->canRemoveAction(), 'REDIRECT');
    }

    public function testCanRemoveAction()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn(1);
        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturn(
                m::mock()->shouldReceive('getResult')
                    ->andReturn(
                        [
                            'isDetached' => false,
                            'hasUsers' => ['test']
                        ]
                    )->getMock());

        $form = m::mock()->shouldReceive('get')
            ->with('messages')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('message')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $this->sm->setService(
            'Helper\Form',
            m::mock()->shouldReceive('createFormWithRequest')
                ->andReturn(
                    $form
                )
                ->once()
                ->getMock()
        );

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->canRemoveAction());
    }

    public function testRemoveActionGet()
    {
        $form = m::mock()->shouldReceive('get')
            ->with('messages')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('message')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->getMock()
                    )
                    ->getMock()
            )->getMock();

        $this->sm->setService(
            'Helper\Form',
            m::mock()->shouldReceive('createFormWithRequest')
                ->andReturn(
                    $form
                )
                ->once()
                ->getMock()
        );

        $this->sut->shouldReceive('getRequest->isPost')->andReturn(false);

        $this->sut->shouldReceive('getViewWithTm')
            ->with(
                [
                    'form' => $form
                ]
            )->once()
            ->andReturn(
                m::mock()->shouldReceive('setTemplate')
                    ->with('partials/form')
                    ->getMock()
            );

        $this->sut->shouldReceive('renderView');

        $this->sut->removeAction();
    }

    public function testRemoveActionPost()
    {
        $form = m::mock()->shouldReceive('get')
            ->with('messages')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('message')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->getMock()
                    )
                    ->getMock()
            )->getMock();

        $this->sm->setService(
            'Helper\Form',
            m::mock()->shouldReceive('createFormWithRequest')
                ->andReturn(
                    $form
                )
                ->once()
                ->getMock()
        );

        $this->sut->shouldReceive('getRequest->isPost')->andReturn(true);
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn(1);
        $this->sut->shouldReceive('handleCommand')
            ->once()
            ->andReturn(
                m::mock()->shouldReceive('isOk')
                    ->andReturn(true)
                    ->getMock()
            );

        $this->sut->shouldReceive('flashMessenger->addSuccessMessage');

        $this->sut->shouldReceive('redirectToRouteAjax')
            ->with(
                'transport-manager/details',
                [
                    'transportManager' => 1
                ]
            )->andReturn('REDIRECT');

        $this->assertEquals($this->sut->removeAction(), 'REDIRECT');
    }
}
