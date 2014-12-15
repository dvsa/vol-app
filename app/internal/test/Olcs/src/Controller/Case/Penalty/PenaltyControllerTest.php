<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Penalty\PenaltyController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Zend\View\Helper\Placeholder;
use Zend\Form\Form;
use Common\Form\Annotation\CustomAnnotationBuilder;

/**
 * Class PenaltyControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PenaltyControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerRouteMatchHelper
     */
    protected $routeMatchHelper;

    public function setUp()
    {
        $this->sut = new PenaltyController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        parent::setUp();
    }

    /**
     * Tests the redirects to applied penalty controller work correctly
     *
     * @dataProvider redirectProvider
     * @param $postedVars
     */
    public function testIndexActionWithRedirect($postedVars)
    {
        $caseId = 29;
        $seriousInfringementId = 1;

        $mockRestData = ['Results' => [0 => ['id' => $seriousInfringementId]]];
        $this->sut->setListData($mockRestData);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->andReturn($postedVars);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'case_penalty_edit',
            [
                'action' => $postedVars['action'],
                'seriousInfringement' => $seriousInfringementId,
                'id' => $postedVars['id']
            ],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->indexAction());
    }

    /**
     * Mocked post data expected by index action redirects
     *
     * @return array
     */
    public function redirectProvider()
    {
        return [
            [['id' => null, 'action' => 'Add']],
            [['id' => 1, 'action' => 'Edit']],
            [['id' => 1, 'action' => 'Delete']]
        ];
    }

    public function testRedirectToIndex()
    {
        $caseId = 29;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            null,
            ['action'=>'index', 'case' => $caseId],
            ['code' => '303'], true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }

    /**
     * Tests the index action
     */
    public function testIndexAction()
    {
        $caseId = 29;
        $case = ['id' => 29, 'version' => 1, 'penaltiesNote' => ''];
        $mockRestData = ['Results' => [0 => ['id' => 1, 'imposedErrus' => [0 => []]]], 'Count' => 1];

        $layout = 'layout/base';
        $headerTemplate = 'partials/header';
        $pageLayout = 'case';
        $pageTitle = 'Page title';
        $pageSubTitle = 'Page sub title';

        $this->sut->setListData($mockRestData);
        $this->sut->setPageLayout($pageLayout);
        $this->sut->setPageTitle($pageTitle);
        $this->sut->setPageSubTitle($pageSubTitle);

        $form = new Form();

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'url' => 'Url'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromPost')->andReturn([]);

        //mock url helper
        $mockPluginManager->shouldReceive('get')->with('url')->andReturnSelf();

        $this->sut->setPluginManager($mockPluginManager);

        //rest call to return penalty data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //placeholders
        $placeholder = new Placeholder();

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock table builder
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->withAnyArgs();

        $formAnnotationBuilder = new CustomAnnotationBuilder();

        $stringHelper = m::mock('\Common\Service\Helper\StringHelperService');
        $stringHelper->shouldReceive('dashToCamel')->withAnyArgs()->andReturn('name');

        $olcsCustomForm = m::mock('\Common\Service\Form\OlcsCustomFormFactory');
        $olcsCustomForm->shouldReceive('createForm')->with('comment')->andReturn($form);

        $mockCaseDataService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseDataService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('FormAnnotationBuilder')->andReturn($formAnnotationBuilder);
        $mockServiceManager->shouldReceive('get')->with('OlcsCustomForm')->andReturn($olcsCustomForm);
        $mockServiceManager->shouldReceive('get')->with('Helper\String')->andReturn($stringHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseDataService);

        $this->sut->setServiceLocator($mockServiceManager);

        $view = $this->sut->indexAction();

        $viewChildren = $view->getChildren();
        $headerView = $viewChildren[0];
        $headerVariables = $headerView->getVariables();
        $layoutView = $viewChildren[1];

        //check we have view models
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $headerView);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $layoutView);

        //check scripts and titles set
        $this->assertEquals($headerVariables['pageTitle'], $pageTitle);
        $this->assertEquals($headerVariables['pageSubTitle'], $pageSubTitle);

        //check templates set
        $this->assertEquals($view->getTemplate(), $layout);
        $this->assertEquals($headerView->getTemplate(), $headerTemplate);
        $this->assertEquals($layoutView->getTemplate(), 'layout/' . $pageLayout);
    }
}
