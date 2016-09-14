<?php

namespace OlcsTest\Controller;

use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccountSelfserve as UpdateDto;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\MyDetailsController as Sut;
use OlcsTest\Bootstrap;

/**
 * Class My Details Controller Test
 */
class MyDetailsControllerTest extends TestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testEditActionForGet()
    {
        $rawEditData = array(
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => array(
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => array(
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ),
            ),
            'translateToWelsh' => 'Y',
        );
        $formattedData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($rawEditData);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->with($formattedData)->once();

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockScript = m::mock();
        $mockScript
            ->shouldReceive('loadFile')
            ->with('my-details')
            ->once();
        $this->sm->setService('Script', $mockScript);

        $view = $this->sut->editAction();

        $this->assertInstanceOf('Common\Form\Form', $view->getVariable('form'));
    }

    public function testEditActionForGetWithError()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->never();

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockFlashMessengerHelper = m::mock();
        $mockFlashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessengerHelper);

        $mockScript = m::mock();
        $mockScript
            ->shouldReceive('loadFile')
            ->with('my-details')
            ->once();
        $this->sm->setService('Script', $mockScript);

        $view = $this->sut->editAction();

        $this->assertInstanceOf('Common\Form\Form', $view->getVariable('form'));
    }

    public function testEditActionForPost()
    {
        $postData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $this->sut->shouldReceive('handleCommand')->with(m::type(UpdateDto::class))->andReturn($response);

        $mockFlashMessengerHelper = m::mock();
        $mockFlashMessengerHelper
            ->shouldReceive('addSuccessMessage')
            ->once()
            ->with('generic.updated.success');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessengerHelper);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('your-account', ['action' => 'edit'], array(), false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->editAction());
    }

    public function testEditActionForPostWithError()
    {
        $postData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);
        $mockForm->shouldReceive('setMessages')->once()->with(m::type('array'));

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getResult')->andReturn(
            [
                'messages' => ['loginId' => 'err']
            ]
        );
        $this->sut->shouldReceive('handleCommand')->with(m::type(UpdateDto::class))->andReturn($response);

        $mockScript = m::mock();
        $mockScript
            ->shouldReceive('loadFile')
            ->with('my-details')
            ->once();
        $this->sm->setService('Script', $mockScript);

        $view = $this->sut->editAction();

        $this->assertInstanceOf('Common\Form\Form', $view->getVariable('form'));
    }

    public function testEditActionForPostWithCancel()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->never();

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('your-account', ['action' => 'edit'], array(), false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->editAction());
    }
}
