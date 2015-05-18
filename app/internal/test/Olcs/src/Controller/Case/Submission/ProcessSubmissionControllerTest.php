<?php
namespace OlcsTest\Controller\Submission;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Processing Submission Controller Test
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ProcessSubmissionControllerTest extends MockeryTestCase
{
    /**
     * @var ProcessingSubmissionController
     */
    protected $sut;

    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    protected $testClass = 'Olcs\Controller\Cases\Submission\ProcessSubmissionController';

    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->sut = new $this->testClass();

        parent::setUp();
    }

    public function testProcessAssign()
    {

        $submissionId = 999;
        $submission = [
            'id' => $submissionId,
            'version' => 4
        ];
        $fields = [];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect', 'FlashMessenger' => 'FlashMessenger']
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('submission')->andReturn($submissionId);
        $mockParams->shouldReceive('fromPost')->with('fields')->andReturn($fields);

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('fetchData')->with($submissionId)->andReturn($submission);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'submission',
            ['action' => 'details'],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockForm = m::mock(FormInterface::class);
        $mockForm->shouldIgnoreMissing($mockForm);

        $stringHelper = new \Common\Service\Helper\StringHelperService();

        // set Service Locator expectations
        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockForm);
        $mockSl->shouldReceive('get')->with('Helper\String')->andReturn($stringHelper);

        $this->sut->setServiceLocator($mockSl);
        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->assignAction();

        $this->assertSame($result->form, $mockForm);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }

    public function testProcessAssignSave()
    {
        $data = ['recipientUser' => 123];
        $submissionId = 999;
        $submission = [
            'id' => $submissionId,
            'version' => 4
        ];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect', 'FlashMessenger' => 'FlashMessenger']
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('submission')->andReturn($submissionId);

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('fetchData')->with($submissionId)->andReturn($submission);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'submission',
            ['action' => 'details'],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockFm = $mockPluginManager->get('FlashMessenger', '');
        $mockFm->shouldReceive('addSuccessMessage')->with(m::type('string'));

        $mockPluginManager->shouldReceive('get')->with('FlashMessenger')->andReturn($mockFm);
        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);
        $this->sut->setPluginManager($mockPluginManager);

        // set business service manager
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();

        $mockResponse = m::mock();
        $mockResponse->shouldReceive('isOk')->andReturn(true);

        $mockSubmissionBusinessService = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $mockSubmissionBusinessService->shouldReceive('process')
            ->once()
            ->with(m::type('array'))
            ->andReturn($mockResponse);

        $bsm->setService('Cases\Submission\Submission', $mockSubmissionBusinessService);

        // set Service Locator expectations
        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with('BusinessServiceManager')->andReturn($bsm);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $this->sut->setServiceLocator($mockSl);

        $this->assertEquals('redirectResponse', $this->sut->processAssignSave($data));
    }

    /**
     * Tests the redirectToIndex method
     */
    public function testRedirectToIndex()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'submission',
            ['action' => 'details'],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }
}
