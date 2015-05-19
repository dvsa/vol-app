<?php
namespace OlcsTest\Controller\Operator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Operator Irfo Gv Permits Controller Test
 */
class OperatorIrfoGvPermitsControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Operator\OperatorIrfoGvPermitsController();
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
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('organisation')->andReturn(123);
        $mockParams->shouldReceive('fromQuery')->with('organisation', null)->andReturn(123);
        $mockParams->shouldReceive('fromRoute')->with('case', null)->andReturnNull();
        $mockParams->shouldReceive('fromQuery')->with('case', null)->andReturnNull();

        $mockDateHelper = m::mock('DateHelper');
        $mockDateHelper->shouldReceive('getDateObject')->andReturnSelf();
        $mockDateHelper->shouldReceive('format')->with('d/m/Y')->andReturn('formatted date');

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Date')->andReturn($mockDateHelper);

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
                [
                    'fields' => [
                        'organisation' => 123,
                        'irfoPermitStatus' => 'irfo_perm_s_pending'
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'createdOn' => '2015-05-05',
                    'expiryDate' => '2015-05-20',
                    'irfoPermitStatus' => 'other_status',
                ],
                [
                    'id' => 987,
                    'createdOn' => '2015-05-05',
                    'expiryDate' => '2015-05-20',
                    'irfoPermitStatus' => 'other_status',
                    'fields' => [
                        'id' => 987,
                        'organisation' => 123,
                        'irfoPermitStatus' => 'other_status',
                        'createdOn' => '2015-05-05',
                        'expiryDate' => '2015-05-20',
                        'idHtml' => 987,
                        'createdOnHtml' => 'formatted date',
                        'expiryDateHtml' => 'formatted date',
                    ],
                ]
            ]
        ];
    }
}
