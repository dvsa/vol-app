<?php
namespace OlcsTest\Controller\Licence\Processing;

use Zend\Form\Annotation\AnnotationBuilder;
use Olcs\Controller\TransportManager\Processing\PublicationController as TmPublicationController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Zend\View\Helper\Placeholder;
use Zend\Form\Form;

/**
 * Class PublicationControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class PublicationControllerTest extends \PHPUnit_Framework_TestCase
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
        $this->sut = new TmPublicationController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();

        parent::setUp();
    }

    /**
     * Tests the index action
     */
    public function testIndexAction()
    {
        $transportManagerId = 1;

        $form = new Form();

        $mockTmData = [
            'id' => $transportManagerId,
        ];

        $action = null;
        $defaultPage = 1;
        $defaultSort = 'createdOn';
        $defaultOrder = 'DESC';
        $defaultLimit = 10;

        $listParams = [
            'page' => $defaultPage,
            'limit' => $defaultLimit,
            'sort' => $defaultSort,
            'order' => $defaultOrder,
            'transportManager' => $transportManagerId
        ];

        $mockListData = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        $event = $this->routeMatchHelper->getMockRouteMatch(
            array(
                'controller' => 'publications',
                'action' => null,
                'transportManager' => $transportManagerId
            )
        );

        $this->sut->setEvent($event);

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'url' => 'url'
            ]
        );

        //mock url helper
        $mockPluginManager->shouldReceive('get')->with('url')->andReturnSelf();

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('fetchList')->with($listParams)->andReturn($mockListData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('transportManager')->andReturn($transportManagerId);

        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);
        $this->sut->setPluginManager($mockPluginManager);

        $mockLicenceService = m::mock('Common\Service\Data\PublicationLink');
        $mockLicenceService->shouldReceive('fetchList')->with($transportManagerId)->andReturn($mockTmData);

        //mock table builder
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->withAnyArgs();

        $mockRouter = m::mock('Zend\Mvc\Router\Http\TreeRouteStack');

        $formAnnotationBuilder = new AnnotationBuilder();

        //mock form helper
        $mockFormHelper = m::mock('Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->andReturn($form);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')->with('router')->andReturn($mockRouter);
        $mockServiceManager->shouldReceive('get')->with('FormAnnotationBuilder')->andReturn($formAnnotationBuilder);
        $mockServiceManager->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockLicenceService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->indexAction());
    }

    /**
     * Tests editAction()
     *
     * @param $pubStatus
     * @param $formName
     *
     * @dataProvider editActionProvider
     */
    public function testEditAction($pubStatus, $formName)
    {
        $id = 1;
        $version = 2;

        $transportManagerId = 7;
        $licenceDescription = 'Licence desc';
        $licenceOrganisation = 'Organisation name';
        $licenceNo = 'OB123456';

        $postArray = [
            'fields' => [
                'text1' => 'text 1 amend',
                'text2' => 'text 2 amend',
                'text3' => 'text 3 amend',
                'id' => $id,
                'version' => $version
            ]
        ];

        $form = m::mock('Zend\Form\Form');
        $form->shouldReceive('getData')->andReturn($postArray);
        $form->shouldReceive('hasAttribute');
        $form->shouldReceive('setAttribute');
        $form->shouldReceive('getFieldsets')->andReturn([]);
        $form->shouldReceive('setData')->withAnyArgs();
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('bind');

        $mockTransportManagerData = [
            'id' => $transportManagerId,
        ];

        $publicationData = [
            'id' => $id,
            'version' => $version,
            'text1' => 'text 1',
            'text2' => 'text 2',
            'text3' => 'text 3',
            'publication' => [
                'pubType' => 'A & D',
                'publicationNo' => 6128,
                'pubDate' => '2014-10-30',
                'pubStatus' => [
                    'description' => 'Publication status',
                    'id' => $pubStatus
                ],
                'trafficArea' => [
                    'name' => 'Traffic area name'
                ],
            ],
            'publicationSection' => [
                'description' => 'Section description'
            ]
        ];

        $this->sut->getRequest()->setMethod('post');
        $this->sut->getRequest()->getPost()->set('fields', $postArray['fields']);

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);
        $mockParams->shouldReceive('fromRoute')->with('transportManager')->andReturn($transportManagerId);
        $mockParams->shouldReceive('fromPost')->andReturn($postArray);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->withAnyArgs()->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('fetchOne')->with($id)->andReturn($publicationData);
        $mockPublicationLink->shouldReceive('update')->with($id, $postArray['fields'])->andReturn($id);

        //licence service
        $mockLicence = m::mock('Common\Service\Data\Licence');
        $mockLicence->shouldReceive('fetchLicenceData')->with($transportManagerId)->andReturn($mockTransportManagerData);

        $this->sut->setPluginManager($mockPluginManager);

        $stringHelper = m::mock('\Common\Service\Helper\StringHelperService');
        $stringHelper->shouldReceive('dashToCamel')->withAnyArgs()->andReturn('name');

        $olcsCustomForm = m::mock('\Common\Service\Form\OlcsCustomFormFactory');
        $olcsCustomForm->shouldReceive('createForm')->with($formName)->andReturn($form);

        $mockRouter = m::mock('Zend\Mvc\Router\Http\TreeRouteStack');

        //mock form helper
        $mockFormHelper = m::mock('Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->andReturn($form);

        //placeholders
        $placeholder = new Placeholder();

        //add placeholders to view helper
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);
        $mockServiceManager->shouldReceive('get')->with('Common\Service\Data\Licence')->andReturn($mockLicence);
        $mockServiceManager->shouldReceive('get')->with('Helper\String')->andReturn($stringHelper);
        $mockServiceManager->shouldReceive('get')->with('OlcsCustomForm')->andReturn($olcsCustomForm);
        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockServiceManager->shouldReceive('get')->with('router')->andReturn($mockRouter);
        $mockServiceManager->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->editAction());
    }

    /**
     * Data provider for testEditAction
     *
     * @return array
     */
    public function editActionProvider()
    {
        return [
            ['pub_s_new', 'Publication'],
            ['pub_s_generated', 'PublicationNotNew'],
            ['pub_s_printed', 'PublicationNotNew']
        ];
    }

    public function testProcessSave()
    {
        $id = 1;
        $version = 2;

        $fields = [
            'text1' => 'text 1',
            'text2' => 'text 2',
            'text3' => 'text 3',
            'id' => $id,
            'version' => $version
        ];

        $data = [
            'fields' => $fields
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect'
            ]
        );

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->withAnyArgs()->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('update')->with($id, $fields)->andReturn($id);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($data));
    }

    /**
     * Tests the delete action
     */
    public function testDeleteAction()
    {
        $id = 1;

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('delete')->with($id)->andReturn([]);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);

        //flash messenger
        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addErrorMessage')->once();

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->deleteAction());
    }

    public function testAddAction()
    {
        $this->assertEquals(false, $this->sut->addAction());
    }
}
