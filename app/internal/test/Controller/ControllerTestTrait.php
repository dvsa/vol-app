<?php

namespace Dvsa\OlcsTest\Controller;

use Mockery as m;

/**
 * Helper functions for testing controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
trait ControllerTestTrait
{
    protected $sut;
    protected $request;
    protected $form;
    protected $view;
    protected $formHelper;
    protected $services = [];

    protected function mockController($className, array $constructorParams = [])
    {
        $this->request = m::mock(\Laminas\Http\Request::class)->makePartial();

        // If constructor params are provided, pass them to the mock, otherwise mock without them
        if (!empty($constructorParams)) {
            $this->sut = m::mock($className, $constructorParams)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        } else {
            $this->sut = m::mock($className)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        }

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);
    }

    protected function mockRender()
    {
        $this->sut->shouldReceive('render')
            ->once()
            ->andReturnUsing(
                function ($view, $form = null) {

                    /**
                     * assign the view variable so we can interrogate it later
                     */
                    $this->view = $view;

                    /*
                     * but also return it, since that's a closer simulation
                     * of what 'render' would normally do
                     */

                    return $this->view;
                }
            );

        return $this->sut;
    }

    protected function setPost($data = [])
    {
        $this->request
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($data);
    }

    protected function shouldRemoveElements($form, $elements)
    {
        $helper = $this->mockFormHelper;
        foreach ($elements as $e) {
            $helper->shouldReceive('remove')
                ->with($form, $e)
                ->andReturn($helper);
        }
    }

    protected function createMockForm($formName)
    {
        $mockForm = m::mock(\Common\Form\Form::class);

        $formHelper = $this->getMockFormHelper();

        $formHelper
            ->shouldReceive('createForm')
            ->with($formName)
            ->andReturn($mockForm)
            ->shouldReceive('createFormWithRequest')
            ->with($formName, $this->request)
            ->andReturn($mockForm);

        return $mockForm;
    }

    protected function getMockFormHelper()
    {
        if ($this->formHelper === null) {
            $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
            $this->setService('Helper\Form', $this->formHelper);
        }
        return $this->formHelper;
    }

    /**
     * @param string $class expected Command class name
     * @param array $expectedDtoData
     * @param array $result to be returned by $response->getResult()
     * @param boolean $ok to be returned by $response->isOk()
     * @param int $times call count
     */
    protected function expectCommand($class, array $expectedDtoData, array $result, $ok = true, $times = 1)
    {
        return $this->mockCommandOrQueryCall('handleCommand', $class, $expectedDtoData, $result, $ok, $times);
    }

    /**
     * @param string $class expected Query class name
     * @param array $expectedDtoData
     * @param array $result to be returned by $response->getResult()
     * @param boolean $ok to be returned by $response->isOk()
     * @param int $times call count
     */
    protected function expectQuery($class, array $expectedDtoData, array $result, $ok = true, $times = 1)
    {
        return $this->mockCommandOrQueryCall('handleQuery', $class, $expectedDtoData, $result, $ok, $times);
    }

    /**
     * @param string $method controller/plugin method to mock 'handleQuery'|'handleCommand'
     * @param string $class expected Query/Command class name
     * @param array $expectedDtoData
     * @param array $result to be returned by $response->getResult()
     * @param boolean $ok to be returned by $response->isOk()
     * @param int $times call count
     */
    private function mockCommandOrQueryCall(
        $method,
        $class,
        array $expectedDtoData,
        array $result,
        $ok = true,
        $times = 1
    ) {
        $response = m::mock()
            ->shouldReceive('isOk')
            ->andReturn($ok)
            ->shouldReceive('isForbidden')
            ->andReturn(false)
            ->shouldReceive('getResult')
            ->andReturn($result)
            ->getMock();

        $this->sut
            ->shouldReceive($method)
            ->with(
                m::on(
                    function ($cmd) use ($expectedDtoData, $class) {
                        $matched = (
                            is_a($cmd, $class)
                            &&
                            $cmd->getArrayCopy() == $expectedDtoData
                        );
                        return $matched;
                    }
                )
            )
            ->times($times)
            ->andReturn($response);
    }
}
