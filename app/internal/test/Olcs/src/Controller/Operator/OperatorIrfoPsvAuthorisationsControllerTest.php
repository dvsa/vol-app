<?php
namespace OlcsTest\Controller\Operator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Operator Irfo Psv Authorisations Controller Test
 */
class OperatorIrfoPsvAuthorisationsControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Operator\OperatorIrfoPsvAuthorisationsController();
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
                        'status' => 'irfo_auth_s_pending',
                        'copiesIssued' => 0,
                        'copiesIssuedHtml' => 0,
                        'copiesIssuedTotal' => 0,
                        'copiesIssuedTotalHtml' => 0,
                        'copiesRequired' => 0,
                        'copiesRequiredTotal' => 0,
                        'copiesRequiredNonChargeable' => 0,
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'createdOn' => '2015-05-05',
                    'status' => 'other_status',
                    'copiesIssued' => 1,
                    'copiesIssuedTotal' => 11,
                    'copiesRequired' => 3,
                    'copiesRequiredTotal' => 33,
                ],
                [
                    'id' => 987,
                    'createdOn' => '2015-05-05',
                    'status' => 'other_status',
                    'copiesIssued' => 1,
                    'copiesIssuedTotal' => 11,
                    'copiesRequired' => 3,
                    'copiesRequiredTotal' => 33,
                    'fields' => [
                        'id' => 987,
                        'organisation' => 123,
                        'status' => 'other_status',
                        'createdOn' => '2015-05-05',
                        'createdOnHtml' => 'formatted date',
                        'copiesIssued' => 1,
                        'copiesIssuedHtml' => 1,
                        'copiesIssuedTotal' => 11,
                        'copiesIssuedTotalHtml' => 11,
                        'copiesRequired' => 3,
                        'copiesRequiredTotal' => 33,
                        'copiesRequiredNonChargeable' => 30,
                    ],
                ]
            ]
        ];
    }
}
