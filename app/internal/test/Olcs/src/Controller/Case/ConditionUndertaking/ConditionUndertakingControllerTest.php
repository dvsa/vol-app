<?php
namespace OlcsTest\Controller\ConditionUndertaking;

use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * ConditionUndertaking controller tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;


    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\ConditionUndertaking\ConditionUndertakingController();
        parent::setUp();
    }

    public function testAlterFormBeforeValidation()
    {
        $mockForm = m::mock('Zend\Form\Form');
        $caseId = 88;
        $licenceId = 99;
        $ocId = 1;
        $mockOcAddresses = [
            'Results' => [
                0 => [
                    'id' => $ocId,
                    'address' => [
                        'countryCode' => 'gb_GB'
                    ]
                ]
            ],
            'Count' => 1
        ];
        $mockCase = [
            'id' => $caseId,
            'licence' => [
                'id' => $licenceId
            ]
        ];

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);

        $this->sut->setPluginManager($mockPluginManager);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('getParam')->with(
            'case'
        )->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with(
            'case'
        )->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'OperatingCentre',
            'GET',
            array('licence' => $licenceId),
            m::type('array')
        )->andReturn($mockOcAddresses);

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Cases',
            'GET',
            array('id' => $caseId),
            m::type('array')
        )->andReturn($mockCase);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($mockCase);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);

        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $mockForm->shouldReceive('getLabel')->andReturn('some_label');
        $mockForm->shouldReceive('setLabel')->with(m::type('string'));

        $options = [
            'Licence' => [
                'label' => 'Licence',
                'options' => [
                    'cat_lic' => 'Licence ' . $licenceId
                ]
            ],
            'OC' => [
                'label' => 'OC Address',
                'options' => [
                    $ocId => ''

                ]
            ]
        ];

        $mockField = m::mock('\Zend\Form\Element');
        $mockField->shouldReceive('setValueOptions')->with($options);

        $mockFields = m::mock('\Zend\Form\Fieldset');
        $mockFields->shouldReceive('get')->with('attachedTo')->andReturn($mockField);

        $mockForm->shouldReceive('get')->with('fields')->andReturn($mockFields);

        $newForm = $this->sut->alterFormBeforeValidation($mockForm, $licenceId);

        $this->assertSame($newForm, $mockForm);
        $this->assertContains('some_label', $newForm->getLabel());

    }

    /**
     * @dataProvider determineFormAttachedToProvider
     * Test for determineFormAttachedTo
     *
     * @param $input
     * @param $expected
     */
    public function testDetermineFormAttachedTo($input, $expected)
    {

        $caseId = 99;
        $mockCase = ['licence' => ['id' => 99]];

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $this->sut->setPluginManager($mockPluginManager);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('getParam')->with(
            'case'
        )->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with(
            'case'
        )->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Cases',
            'GET',
            array('id' => $caseId),
            m::type('array')
        )->andReturn($mockCase);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($mockCase);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);

        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals($expected, $this->sut->determineFormAttachedTo($input));

    }

    /**
     * @dataProvider formLoadProvider
     * Test for processLoad
     *
     * @param $input
     * @param $expected
     */
    public function testProcessLoad($input, $expected)
    {
        $caseId = 99;
        $mockCase = ['licence' => ['id' => 99]];

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $this->sut->setPluginManager($mockPluginManager);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('getParam')->with(
            'case',
            ''
        )->andReturn($caseId);
        $mockParams->shouldReceive('fromQuery')->with(
            'case',
            ''
        )->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with(
            'case'
        )->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Cases',
            'GET',
            array('id' => $caseId),
            m::type('array')
        )->andReturn($mockCase);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($mockCase);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);

        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals($expected, $this->sut->processLoad($input));

    }

    /**
     * @dataProvider formSaveProvider
     * Test for processSave
     *
     * @param $input
     * @param $expected
     */
    public function testProcessSaveUpdate($input)
    {
        $mockDataToSave = ['id' => 99];

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->with(m::type('string'));

        $mockRedirectPlugin = $mockPluginManager->get('redirect', '');

        $mockRedirectPlugin->shouldReceive('toRouteAjax')->with(
            '',
            ['action' => 'index', 'id'=>''],
            ['code' => 303],
            true
        )->andReturn('mockResponse');
        $this->sut->setPluginManager($mockPluginManager);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'ConditionUndertaking',
            'PUT',
            $mockDataToSave,
            ""
        )->andReturnNull();

        $mockDataHelper = m::mock('DataHelper');
        $mockDataHelper->shouldReceive('processDataMap')->with(
            m::Type('array'),
            m::Type('array'),
            m::Type('string')
        )->andReturn($mockDataToSave);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('mockResponse', $this->sut->processSave($input));

    }


    /**
     * @dataProvider formSaveProvider
     * Test for processSave
     *
     * @param $input
     * @param $expected
     */
    public function testProcessSaveInsert($input)
    {
        $mockDataToSave = $input;

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->with(m::type('string'));

        $mockRedirectPlugin = $mockPluginManager->get('redirect', '');
        $mockRedirectPlugin->shouldReceive('toRouteAjax')->with(
            '',
            ['action' => 'index', 'id'=>''],
            ['code' => 303],
            true
        )->andReturn('mockResponse');

        $this->sut->setPluginManager($mockPluginManager);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'ConditionUndertaking',
            'POST',
            $mockDataToSave,
            ""
        )->andReturn(['id' => 99]);

        $mockDataHelper = m::mock('DataHelper');
        $mockDataHelper->shouldReceive('processDataMap')->with(
            m::Type('array'),
            m::Type('array'),
            m::Type('string')
        )->andReturn($mockDataToSave);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('Helper\Data')->andReturn($mockDataHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('mockResponse', $this->sut->processSave($input));

    }

    public function determineFormAttachedToProvider()
    {
        $oc = 'foo';
        return [
            [
                ['foo'], ['foo', 'fields' => ['licence' => 99, ]]
            ],
            [
                ['fields' => ['attachedTo' => 'cat_lic']], ['fields' => ['attachedTo' => 'cat_lic', 'licence' => 99]]
            ],
            [
                ['fields' => ['attachedTo' => 'something_else']], ['fields' => ['attachedTo' => '',
                'licence' => 99]]
            ],
            [
                ['fields' => ['operatingCentre' => $oc, 'attachedTo' => 'something_else']],
                ['fields' => ['operatingCentre' => $oc, 'attachedTo' => $oc, 'licence' => 99]]
            ]
        ];
    }

    public function formLoadProvider()
    {
        return [
            [
                ['foo'], ['foo', 'fields' => ['licence' => 99, 'case' => 99], 'case' => 99, 'base' => ['case' => 99]]
            ],
            [
                ['fields' => ['attachedTo' => 'cat_lic']],
                ['fields' =>
                    [
                        'attachedTo' => 'cat_lic',
                        'licence' => 99,
                        'case' => 99
                    ],
                    'case' => 99,
                    'base' => ['case' => 99]
                ]
            ],
            [
                ['fields' => ['attachedTo' => 'something_else']],
                ['fields' => ['attachedTo' => '','licence' => 99, 'case' => 99], 'case' => 99, 'base' => ['case' => 99]]
            ]
        ];
    }

    public function formSaveProvider()
    {
        return [
            [
                ['fields' => ['attachedTo' => 'cat_lic']]
            ],
            [
                ['fields' => ['attachedTo' => 'something_else']]
            ]

        ];
    }
}
