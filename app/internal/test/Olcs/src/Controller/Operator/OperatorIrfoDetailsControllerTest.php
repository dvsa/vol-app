<?php
namespace OlcsTest\Controller\Operator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Operator Irfo Details Controller Test
 */
class OperatorIrfoDetailsControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Operator\OperatorIrfoDetailsController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        parent::setUp();
    }

    /**
    * @dataProvider processLoadDataProvider
    *
    * @param $inData
    * @param $outData
    */
    public function testProcessLoad($inData, $outData)
    {
        $phoneContactFields = ['phone_contact_1' => 'phone contact 1'];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case', null)->andReturnNull();
        $mockParams->shouldReceive('fromQuery')->with('case', null)->andReturnNull();

        $mockPhoneContactService = m::mock('Lva\PhoneContact');
        $mockPhoneContactService->shouldReceive('mapPhoneFieldsFromDb')->andReturn($phoneContactFields);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Lva\PhoneContact')->andReturn($mockPhoneContactService);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        // we don't need to test what parent's processLoad does
        $result = array_diff_key($this->sut->processLoad($inData), ['base' => null]);

        $this->assertEquals($outData, $result);
    }

    public function processLoadDataProvider()
    {
        return [
            // add
            [
                [],
                []
            ],
            // edit
            [
                [
                    'id' => 987,
                    'irfoContactDetails' => [
                        'address' => 'some address',
                        'emailAddress' => 'test@test.me',
                        'phoneContacts' => [
                            ['id' => 1]
                        ]
                    ]
                ],
                [
                    'id' => 987,
                    'idHtml' => 987,
                    'irfoContactDetails' => [
                        'address' => 'some address',
                        'emailAddress' => 'test@test.me',
                        'phoneContacts' => [
                            ['id' => 1]
                        ]
                    ],
                    'fields' => [
                        'id' => 987,
                        'idHtml' => 987,
                        'irfoContactDetails' => [
                            'address' => 'some address',
                            'emailAddress' => 'test@test.me',
                            'phoneContacts' => [
                                ['id' => 1]
                            ]
                        ],
                        'address' => 'some address',
                        'contact' => [
                            'email' => 'test@test.me',
                            'phone_contact_1' => 'phone contact 1'
                        ],
                    ],
                    'address' => 'some address',
                    'contact' => [
                        'email' => 'test@test.me',
                        'phone_contact_1' => 'phone contact 1'
                    ],
                ]
            ]
        ];
    }

    public function testProcessSave()
    {
        $organisationId = 5;
        $postData = [
            'fields' => ['some fields'],
            'address' => ['some address'],
            'contact' => ['some contact'],
        ];

        $existingData = ['id' => $organisationId];
        $this->sut->setLoadedData($existingData);

        $mockIrfoDetailsResponse = m::mock('\Common\BusinessService\Response');
        $mockIrfoDetailsResponse->shouldReceive('isOk')
            ->once()
            ->andReturn(true);

        $mockIrfoDetailsService = m::mock('Operator\IrfoDetails');
        $mockIrfoDetailsService->shouldReceive('process')->once()
            ->with(
                [
                    'id' => $organisationId,
                    'data' => array_merge($existingData, $postData['fields']),
                    'address' => $postData['address'],
                    'contact' => $postData['contact'],
                ]
            )
            ->andReturn($mockIrfoDetailsResponse);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('BusinessServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Operator\IrfoDetails')->andReturn($mockIrfoDetailsService);

        $this->sut->setServiceLocator($mockServiceManager);

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'FlashMessenger' => 'FlashMessenger',
                'params' => 'Params'
            ]
        );

        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->once();
        $mockFlashMessenger->shouldReceive('addErrorMessage')->never();

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->once()->with(
            'operator/irfo/details',
            ['action'=>'edit'],
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('organisation')->andReturn($organisationId);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->processSave($postData));
    }
}
