<?php

namespace OlcsTest\Controller\Lva\Licence;

use Olcs\Controller\Lva\Licence\TypeOfLicenceController;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use ZfcRbac\Mvc\Controller\Plugin\IsGranted;
use Zend\View\Model\ViewModel;
use Mockery as m;

/**
 * Test Licence Type Of Licence Controller
 */
class TypeOfLicenceControllerTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /** @var  TypeOfLicenceController|m\MockInterface */
    private $sut;
    /** @var  \Zend\ServiceManager\ServiceLocatorInterface|m\MockInterface */
    private $mockSl;
    /** @var  \Zend\Mvc\Controller\PluginManager|m\MockInterface */
    private $mockPluginManager;
    /** @var  m\MockInterface */
    private $mockIsGrantedPlgn;

    public function setUp()
    {
        $this->mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class)->makePartial();
        $this->mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();

        //  mock plugins
        $this->mockPluginManager = (new ControllerPluginManagerHelper)->getMockPluginManager(
            [
                'handleQuery' => 'handleQuery',
                'url' => 'Url',
                'params' => 'Params',
                'prg' => 'prg',
            ]
        );

        $urlPlugin = $this->mockPluginManager->get('url', '');
        $urlPlugin->shouldReceive('fromRoute')->andReturn('foo');

        $this->mockIsGrantedPlgn = m::mock(IsGranted::class);
        $this->mockPluginManager
            ->shouldReceive('get')
            ->with('isGranted', null)
            ->andReturn($this->mockIsGrantedPlgn);


        //  instance of tested class
        $this->sut = new TypeOfLicenceController();
        $this->sut->setServiceLocator($this->mockSl);
        $this->sut->setPluginManager($this->mockPluginManager);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Tests index details action for licence entity Non Partner
     */
    public function testCannotUpdateLicenceTypeReturnsMessageToChangeToVariation()
    {
        $entity = 'licence';
        $entityId = 7;

        $mockResult = [
            'canUpdateLicenceType' => false,
            'doesChangeRequireVariation' => true,
            'canBecomeSpecialRestricted' => true,
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery', '');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('prg', '');
        $mockQueryHandler->shouldReceive()->andReturn(false);

        $mockParams = $this->mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        $this->mockSl
            ->shouldReceive('get')
            ->with('Helper\Guidance')
            ->andReturn(
                m::mock()
                    ->shouldReceive('append')
                    ->with('business-type.locked.message')
                    ->once()
                    ->getMock()
            );


        //  call & check
        /** @var ViewModel $result */
        $result = $this->sut->indexAction();
        $children = $result->getChildren();

        $content = reset($children);

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertInstanceOf(ViewModel::class, $content);

        static::assertEquals($result->pageTitle, 'MYCOMPANY');
        static::assertEquals($result->pageSubtitle, 'OB12345');
        static::assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        static::assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        static::assertEquals($content->operatingCentresTable, 'operatingCentresTableResult');
    }


}
