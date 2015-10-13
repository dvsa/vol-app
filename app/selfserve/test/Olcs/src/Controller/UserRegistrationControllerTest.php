<?php
/**
 * Class User Registration Controller Test
 */
namespace OlcsTest\Controller;

use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as CreateDto;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\UserRegistrationController as Sut;
use OlcsTest\Bootstrap;

/**
 * Class User Registration Controller Test
 */
class UserRegistrationControllerTest extends TestCase
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

    public function testAddActionForGet()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockScript = m::mock();
        $mockScript
            ->shouldReceive('loadFile')
            ->with('user-registration')
            ->once();
        $this->sm->setService('Script', $mockScript);

        $view = $this->sut->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
    }

    public function testAddActionForPostWithCancel()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->never();

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with(null, ['action' => 'add'], array(), false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->addAction());
    }

    public function testAddActionForPost()
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'N',
                'organisationName' => 'Org name',
                'businessType' => 'type'
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
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $this->sut->shouldReceive('handleCommand')->with(m::type(CreateDto::class))->andReturn($response);

        $view = $this->sut->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
    }

    public function testAddActionForPostWithError()
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo'
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
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleCommand')->with(m::type(CreateDto::class))->andReturn($response);

        $mockFlashMessengerHelper = m::mock();
        $mockFlashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessengerHelper);

        $mockScript = m::mock();
        $mockScript
            ->shouldReceive('loadFile')
            ->with('user-registration')
            ->once();
        $this->sm->setService('Script', $mockScript);

        $view = $this->sut->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
    }
}
