<?php
namespace OlcsTest\Controller\ConditionUndertaking;

use OlcsTest\Bootstrap;
use Mockery as m;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;
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

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('HelperService')->andReturnSelf();
        $mockServiceManager->shouldReceive('getHelperService')->with('RestHelper')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get->getHelperService')->with('RestService')->andReturn($mockRestHelper);

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

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('HelperService')->andReturnSelf();
        $mockServiceManager->shouldReceive('getHelperService')->with('RestHelper')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get->getHelperService')->with('RestService')->andReturn($mockRestHelper);

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

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('HelperService')->andReturnSelf();
        $mockServiceManager->shouldReceive('getHelperService')->with('RestHelper')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get->getHelperService')->with('RestService')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals($expected, $this->sut->processLoad($input));

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
                ['fields' => ['attachedTo' => 'cat_lic']], ['fields' => ['attachedTo' => 'cat_lic', 'licence' => 99, 'case' => 99], 'case' => 99, 'base' => ['case' => 99]]
            ],
            [
                ['fields' => ['attachedTo' => 'something_else']], ['fields' => ['attachedTo' => '',
                'licence' => 99, 'case' => 99], 'case' => 99, 'base' => ['case' => 99]]
            ]
        ];
    }
}
