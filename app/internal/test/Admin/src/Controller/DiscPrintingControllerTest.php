<?php

/**
 * Disc Printing Controller test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace AdminTest\Controller;

use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Entity\LicenceEntityService;

/**
 * Disc Printing Controller test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DiscPrintingControllerTest extends AbstractAdminControllerTest
{
    public function setUp()
    {
        $this->markTestSkipped();
        $this->sut = m::mock('Admin\Controller\DiscPrintingController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->mockForm = m::mock();
        $this->mockLicenceType = m::mock();
        $this->mockOperatorLocation = m::mock();
        $this->mockOperatorType = m::mock();
        $this->mockDiscSequence = m::mock();
        $this->mockStartNumber = m::mock();
        $this->mockEndNumber = m::mock();
        $this->mockEndNumberIncreased = m::mock();
        $this->mockTotalPages = m::mock();
        $this->mockParams = m::mock();

        $this->mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $this->mockFormHelper);

        $this->sm->setService(
            'ZfcRbac\Service\AuthorizationService',
            m::mock()
            ->shouldReceive('isGranted')
            ->andReturn(true)
            ->getMock()
        );
    }

    /**
     * Unload rbac mocking
     * 
     */
    public function tearDown()
    {
        $this->getServiceManager()->setService('ZfcRbac\Service\AuthorizationService', null);
    }

    /**
     * Test index action
     * @group discPrinting
     */
    public function testIndexAction()
    {
        $mockForm = m::mock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('DiscPrinting')
            ->andReturn($mockForm)
            ->once()
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->once()
                ->getMock()
            )
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('success', null)
                ->andReturn(true)
                ->once()
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['disc-printing'])
            ->once()
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Test disc prefixes list action
     * @group discPrinting
     */
    public function testDiscPrefixesListAction()
    {

        $data = [
            'niFlag' => 'N',
            'operatorType' => 'lcat_gv',
            'licenceType' => 'ltyp_r'
        ];
        $this->mockGetFlattenParamsMethod($data);

        $discPrefixes = [
            1 => 'OK',
            2 => 'OB',
            3 => 'AB',
            4 => 'ZY'
        ];

        $this->sm->setService(
            'Admin\Service\Data\DiscSequence',
            m::mock()
            ->shouldReceive('fetchListOptions')
            ->with(
                [
                    'niFlag' => $data['niFlag'],
                    'goodsOrPsv' => $data['operatorType'],
                    'licenceType' => $data['licenceType']
                ]
            )
            ->andReturn($discPrefixes)
            ->getMock()
        );

        $response = $this->sut->discPrefixesListAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals(count($result), 4);
        // result should be sorted alphabetically by label with keys preserved
        $this->assertEquals(
            $result,
            [
                ['value' => 3, 'label' => 'AB'],
                ['value' => 2, 'label' => 'OB'],
                ['value' => 1, 'label' => 'OK'],
                ['value' => 4, 'label' => 'ZY']
            ]
        );
    }

    /**
     * Test disc prefixes list action with bad params
     * @group discPrinting
     */
    public function testDiscPrefixesListActionWithBadParams()
    {
        $data = [
            'niFlag' => 'N',
            'licenceType' => 'ltyp_r',
        ];
        $this->mockGetFlattenParamsMethod($data);

        $response = $this->sut->discPrefixesListAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals(count($result), 0);
    }

    /**
     * Mock getFlattenParams method
     */
    protected function mockGetFlattenParamsMethod($data)
    {
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('getPost')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('toArray')
                    ->andReturn($data)
                    ->getMock()
                )
                ->getMock()
            );
    }

    /**
     * Test confirm disc printing
     * 
     * @dataProvider providerOperatorType
     * @group discPrinting
     */
    public function testConfirmDiscPrintingAction($operatorType)
    {
        $data = [
            'niFlag' => 'N',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'isSuccessfull' => true,
            'discSequence' => 1,
            'endNumber' => 2,
            'startNumber' => 1,
            'startNumberEntered' => 1,
            'operatorType' => $operatorType,
            'discPrefix' => 'AB'
        ];
        $this->mockGetFlattenParamsMethod($data);

        $discsToPrint = [['id' => 1]];
        $this->sm->setService(
            'Admin\Service\Data\DiscSequence',
            m::mock()
            ->shouldReceive('setNewStartNumber')
            ->with($data['licenceType'], $data['discSequence'], $data['endNumber'] + 1)
            ->once()
            ->getMock()
        );

        if ($operatorType === LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $discServiceName = 'Admin\Service\Data\PsvDisc';
            $mockDiscService = m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with($data['licenceType'], $data['discPrefix'])
                ->andReturn($discsToPrint)
                ->once()
                ->getMock();
        } else {
            $discServiceName = 'Admin\Service\Data\GoodsDisc';
            $mockDiscService = m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with($data['niFlag'], $data['operatorType'], $data['licenceType'], $data['discPrefix'])
                ->andReturn($discsToPrint)
                ->once()
                ->getMock();
        }
        $this->sm->setService(
            $discServiceName,
            $mockDiscService
                ->shouldReceive('setIsPrintingOffAndAssignNumber')
                ->with($discsToPrint, $data['startNumber'])
                ->once()
                ->getMock()
        );

        $response = $this->sut->confirmDiscPrintingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals(isset($result['status']), false);
    }

    /**
     * Test confirm disc printing unsuccessfull
     * @group discPrinting
     */
    public function testConfirmDiscPrintingActionUnsuccessfull()
    {
        $data = [
            'niFlag' => 'N',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'isSuccessfull' => false,
            'discSequence' => 1,
            'endNumber' => 2,
            'startNumber' => 1,
            'startNumberEntered' => 1,
            'operatorType' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'discPrefix' => 'AB'
        ];
        $this->mockGetFlattenParamsMethod($data);

        $discsToPrint = [['id' => 1]];
        $this->sm->setService('Admin\Service\Data\DiscSequence', m::mock());

        $this->sm->setService(
            'Admin\Service\Data\GoodsDisc',
            m::mock()
            ->shouldReceive('getDiscsToPrint')
            ->with($data['niFlag'], $data['operatorType'], $data['licenceType'], $data['discPrefix'])
            ->andReturn($discsToPrint)
            ->once()
            ->shouldReceive('setIsPrintingOff')
            ->with($discsToPrint)
            ->once()
            ->getMock()
        );

        $response = $this->sut->confirmDiscPrintingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals($result, []);
        $this->assertEquals(isset($result['status']), false);
    }

    /**
     * Test confirm disc printing with exception
     * @group discPrinting
     */
    public function testConfirmDiscPrintingActionWithException()
    {
        $data = [
            'niFlag' => 'N',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'isSuccessfull' => true,
            'discSequence' => 1,
            'endNumber' => 2,
            'startNumber' => 1,
            'startNumberEntered' => 1,
            'operatorType' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'discPrefix' => 'AB'
        ];
        $this->mockGetFlattenParamsMethod($data);

        $discsToPrint = [['id' => 1]];
        $this->sm->setService('Admin\Service\Data\DiscSequence', m::mock());

        $this->sm->setService(
            'Admin\Service\Data\GoodsDisc',
            m::mock()
            ->shouldReceive('getDiscsToPrint')
            ->with($data['niFlag'], $data['operatorType'], $data['licenceType'], $data['discPrefix'])
            ->andReturn($discsToPrint)
            ->once()
            ->shouldReceive('setIsPrintingOffAndAssignNumber')
            ->andThrow(new \Exception)
            ->once()
            ->getMock()
        );

        $response = $this->sut->confirmDiscPrintingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals($result['status'], 500);
    }

    /**
     * Test confirm disc printing with exception
     * @dataProvider providerOperatorType
     * @group discPrinting
     */
    public function testDiscNumberingAction($operatorType)
    {
        $expectedNumbering = [
            'startNumber' => 2,
            'discsToPrint' => 2,
            'endNumber' => 7,
            'originalEndNumber' => 3,
            'endNumberIncreased' => 3,
            'totalPages' => 1
        ];
        $data = [
            'discSequence' => 1,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'startNumber' => $expectedNumbering['startNumber'],
            'operatorType' => $operatorType,
            'discPrefix' => 'AB',
            'niFlag' => 'N'
        ];

        $this->mockProcessDiscNumberingMethod($data);

        $response = $this->sut->discNumberingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals($result, $expectedNumbering);
    }

    /**
     * Test confirm disc printing with exception
     * @group discPrinting
     */
    public function testDiscNumberingActionWithBadParams()
    {
        $data = [
            'discSequence' => 1,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'startNumber' => 1,
            'operatorType' => '',
            'discPrefix' => '',
            'niFlag' => ''
        ];

        $this->mockProcessDiscNumberingMethod($data);

        $response = $this->sut->discNumberingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals($result, []);
    }

    /**
     * Test disc printing with no discs to print
     * @group discPrinting
     */
    public function testDiscNumberingActionWithNoDiscsToPrint()
    {
        $data = [
            'discSequence' => 1,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'startNumber' => 1,
            'operatorType' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'discPrefix' => 'AB',
            'niFlag' => 'N'
        ];
        $expectedNumbering = [
            'startNumber' => 1,
            'discsToPrint' => 0,
            'endNumber' => 0,
            'originalEndNumber' => 0,
            'endNumberIncreased' => 0,
            'totalPages' => 0
        ];

        $this->mockProcessDiscNumberingMethod($data, []);

        $response = $this->sut->discNumberingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals($result, $expectedNumbering);
    }

    /**
     * Test disc printing with increasing start number
     * @group discPrinting
     */
    public function testDiscNumberingActionWithIncreasingStartNumber()
    {
        $data = [
            'discSequence' => 1,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'startNumber' => 2,
            'startNumberEntered' => 3,
            'endNumber' => 3,
            'operatorType' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'discPrefix' => 'AB',
            'niFlag' => 'N'
        ];
        $expectedNumbering = [
            'startNumber' => 3,
            'discsToPrint' => 2,
            'endNumber' => 8,
            'endNumberIncreased' => 4,
            'originalEndNumber' => 3,
            'totalPages' => 1
        ];

        $this->mockProcessDiscNumberingMethod($data);

        $response = $this->sut->discNumberingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals($result, $expectedNumbering);
    }

    /**
     * Test disc printing with decreasing start number
     * @group discPrinting
     */
    public function testDiscNumberingActionWithDecreasingStartNumber()
    {
        $data = [
            'discSequence' => 1,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'startNumber' => 2,
            'startNumberEntered' => 1,
            'endNumber' => 3,
            'operatorType' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'discPrefix' => 'AB',
            'niFlag' => 'N'
        ];
        $this->mockProcessDiscNumberingMethod($data);

        $response = $this->sut->discNumberingAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $response);
        $result = json_decode($response->serialize(), true);
        $this->assertEquals(is_array($result), true);
        $this->assertEquals(isset($result['error']), true);
        $this->assertEquals($result['error'], 'Decreasing the start number is not permitted');

        $this->allParams['startNumberEntered'] = 1;
    }

    /**
     * Mock processDiscNumbering method
     */
    public function mockProcessDiscNumberingMethod($data, $discsToPrint = [[], []])
    {
        $this->mockGetFlattenParamsMethod($data);

        $this->sm->setService(
            'Admin\Service\Data\DiscSequence',
            m::mock()
            ->shouldReceive('getDiscNumber')
            ->with($data['discSequence'], $data['licenceType'])
            ->andReturn($data['startNumber'])
            ->getMock()
        );

        if ($data['operatorType'] == LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $this->sm->setService(
                'Admin\Service\Data\PsvDisc',
                m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with($data['licenceType'], $data['discPrefix'])
                ->andReturn($discsToPrint)
                ->getMock()
            );
        } else {
            $this->sm->setService(
                'Admin\Service\Data\GoodsDisc',
                m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with(
                    $data['niFlag'],
                    $data['operatorType'],
                    $data['licenceType'],
                    $data['discPrefix']
                )
                ->andReturn($discsToPrint)
                ->getMock()
            );
        }
    }

    /**
     * Test index action with no discs to print received
     * @dataProvider providerGoodsOrPsv
     * @group discPrinting
     */
    public function testIndexActionWithPrintGoodsDiscs(
        $operatorType,
        $fileName,
        $description,
        $template,
        $bookmark,
        $printDescription
    ) {
        $data = [
            'operatorLocation' => 'N',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            'operatorType' => $operatorType,
            'startNumber' => '1',
            'endNumber' => '',
            'totalPages' => '',
            'discSequence' => 1,
            'discPrefix' => 'AB'
        ];

        $post = [
            'operator-location' => ['niFlag' => $data['operatorLocation']],
            'operator-type' => ['goodsOrPsv' => $data['operatorType']],
            'licence-type' => ['licenceType' => $data['licenceType']],
            'disc-numbering' => ['startNumberEntered' => 1],
            'prefix' => ['discSequence' => $data['discSequence']]
        ];

        if ($operatorType == LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $discsToPrint = [
                ['id' => 1, 'licence' => ['id' => 1]],
                ['id' => 2, 'licence' => ['id' => 2]]
            ];
        } else {
            $discsToPrint = [
                ['id' => 1, 'licenceVehicle' => ['licence' => ['id' => 1]]],
                ['id' => 2, 'licenceVehicle' => ['licence' => ['id' => 2]]]
            ];
        }

        $queries = [
            '1' => [
                'licence'=> 1,
                'user' => 1
            ],
            '2' => [
                'licence'=> 2,
                'user' => 1
            ],
        ];

        $bookmarks = [
            1 => [
                'NO_DISCS_PRINTED' => [
                    'count' => 1
                ]
            ],
            2 => [
                'NO_DISCS_PRINTED' => [
                    'count' => 1
                ]
            ]
        ];

        $this->mockAlterFormBeforeValidation();
        $this->mockPostSetFormData($data);

        $this->mockForm
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->getMock();

        $this->mockParams
            ->shouldReceive('fromRoute')
            ->with('success', null)
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('DiscPrinting')
            ->andReturn($this->mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('params')
            ->andReturn($this->mockParams)
            ->shouldReceive('loadScripts')
            ->with(['disc-printing', 'disc-printing-popup'])
            ->shouldReceive('renderView')
            ->andReturn('view')
            ->once()
            ->shouldReceive('getLoggedInUser')
            ->andReturn(1)
            ->shouldReceive('makeRestCall')
            ->with('BookmarkSearch', 'GET', [], [])
            ->andReturn(
                [
                    $bookmark => [
                        ['foo' => 'bar']
                    ]
                ]
            );

        $this->sm->setService(
            'Admin\Service\Data\DiscSequence',
            m::mock()
            ->shouldReceive('getDiscPrefix')
            ->with($data['discSequence'], $data['licenceType'])
            ->andReturn($data['discPrefix'])
            ->getMock()
        );

        if ($operatorType == LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $this->sm->setService(
                'Admin\Service\Data\PsvDisc',
                m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with($data['licenceType'], $data['discPrefix'])
                ->andReturn($discsToPrint)
                ->shouldReceive('setIsPrintingOn')
                ->with($discsToPrint)
                ->getMock()
            );
        } else {
            $this->sm->setService(
                'Admin\Service\Data\GoodsDisc',
                m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with(
                    "N",
                    $data['operatorType'],
                    $data['licenceType'],
                    $data['discPrefix']
                )
                ->andReturn($discsToPrint)
                ->shouldReceive('setIsPrintingOn')
                ->with($discsToPrint)
                ->getMock()
            );
        }

        $file = new \Dvsa\Olcs\DocumentShare\Data\Object\File();
        $file->setContent('dummy content');

        // disc IDs we expect to query against
        $queryData = [1, 2];

        $this->sm->setService(
            'Document',
            m::mock()
            ->shouldReceive('populateBookmarks')
            ->andReturn('replaced content')
            ->once()
            ->shouldReceive('getBookmarkQueries')
            ->with($file, $queryData)
            ->andReturn([])
            ->once()
            ->getMock()
        );

        $this->sm->setService(
            'ContentStore',
            m::mock()
            ->shouldReceive('read')
            ->with('/templates/' . $template)
            ->andReturn($file)
            ->once()
            ->getMock()
        );

        $this->sm->setService(
            'Helper\DocumentGeneration',
            m::mock()
            ->shouldReceive('uploadGeneratedContent')
            ->with('replaced content', 'documents', $template)
            ->andReturn('FakeFile')
            ->once()
            ->getMock()
        );

        $this->sm->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->with('FakeFile', $printDescription)
            ->once()
            ->getMock()
        );

        $mockVehicleList = m::mock()
            ->shouldReceive('setQueryData')
            ->with($queries)
            ->andReturnSelf()
            ->shouldReceive('setTemplate')
            ->with($fileName)
            ->andReturnSelf()
            ->shouldReceive('setDescription')
            ->with($description)
            ->andReturnSelf()
            ->shouldReceive('generateVehicleList')
            ->once()
            ->getMock();

        if ($operatorType == LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $mockVehicleList
                ->shouldReceive('setBookmarkData')
                ->with($bookmarks)
                ->andReturnSelf()
                ->getMock();
        }

        $this->sm->setService('VehicleList', $mockVehicleList);

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Provider for disc printing
     */
    public function providerGoodsOrPsv()
    {
        return [
           'Goods' => [
               LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
               'GVVehiclesList',
               'Goods Vehicle List',
               'GVDiscTemplate.rtf',
               'Disc_List',
               'Goods Disc List'
           ],
           'PSV' => [
               LicenceEntityService::LICENCE_CATEGORY_PSV,
               'PSVVehiclesList',
               'PSV Vehicle List',
               'PSVDiscTemplate.rtf',
               'Psv_Disc_Page',
               'PSV Disc List'
           ]
        ];
    }

    /**
     * Test index action with no discs to print received
     * @group discPrinting
     * @dataProvider providerOperatorType
     */
    public function testIndexActionWithNoDiscsToPrint($operatorType)
    {
        $data = [
            'operatorLocation' => 'N',
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            'operatorType' => $operatorType,
            'startNumber' => '1',
            'endNumber' => '',
            'totalPages' => '',
            'discSequence' => 1,
            'discPrefix' => 'AB'
        ];

        $post = [
            'operator-location' => ['niFlag' => $data['operatorLocation']],
            'operator-type' => ['goodsOrPsv' => $data['operatorType']],
            'licence-type' => ['licenceType' => $data['licenceType']],
            'disc-numbering' => ['startNumberEntered' => 1],
            'prefix' => ['discSequence' => $data['discSequence']]
        ];

        $this->mockAlterFormBeforeValidation();
        $this->mockPostSetFormData($data);

        $this->mockForm
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->getMock();

        $this->mockParams
            ->shouldReceive('fromRoute')
            ->with('success', null)
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('DiscPrinting')
            ->andReturn($this->mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('params')
            ->andReturn($this->mockParams)
            ->shouldReceive('loadScripts')
            ->with(['disc-printing'])
            ->shouldReceive('renderView')
            ->andReturn('view')
            ->once();

        $this->sm->setService(
            'Admin\Service\Data\DiscSequence',
            m::mock()
            ->shouldReceive('getDiscPrefix')
            ->with($data['discSequence'], $data['licenceType'])
            ->andReturn($data['discPrefix'])
            ->getMock()
        );

        if ($operatorType == LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->sm->setService(
                'Admin\Service\Data\GoodsDisc',
                m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with(
                    "N",
                    $data['operatorType'],
                    $data['licenceType'],
                    $data['discPrefix']
                )
                ->andReturn([])
                ->getMock()
            );
        } else {
            $this->sm->setService(
                'Admin\Service\Data\PsvDisc',
                m::mock()
                ->shouldReceive('getDiscsToPrint')
                ->with(
                    $data['licenceType'],
                    $data['discPrefix']
                )
                ->andReturn([])
                ->getMock()
            );
        }

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Operator type provider
     */
    public function providerOperatorType()
    {
        return [
            [LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE],
            [LicenceEntityService::LICENCE_CATEGORY_PSV]
        ];
    }

    /**
     * Test index action with POST with bad params
     * @group discPrinting
     */
    public function testIndexActionWithPostWithBadParams()
    {
        $post = [];

        $data = [
            'operatorLocation' => '',
            'licenceType' => '',
            'operatorType' => '',
            'startNumber' => '1',
            'endNumber' => '',
            'totalPages' => '',
            'discSequence' => '',
            'discPrefix' => 'AB'
        ];

        $this->mockAlterFormBeforeValidation();
        $this->mockPostSetFormData($data, true);

        $this->mockForm
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->getMock();

        $this->mockParams
            ->shouldReceive('fromRoute')
            ->with('success', null)
            ->andReturn(false)
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('DiscPrinting')
            ->andReturn($this->mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('params')
            ->andReturn($this->mockParams)
            ->shouldReceive('loadScripts')
            ->with(['disc-printing'])
            ->shouldReceive('renderView')
            ->andReturn('view')
            ->once();

        $this->assertEquals('view', $this->sut->indexAction());
    }

    /**
     * Mock alterFormBeforeValidation method
     */
    protected function mockAlterFormBeforeValidation()
    {
        $this->mockLicenceType
            ->shouldReceive('getValueOptions')
            ->andReturn(['ltyp_sr' => 'ltyp_sr'])
            ->shouldReceive('setValueOptions')
            ->with([])
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('licenceType')
                ->andReturn($this->mockLicenceType)
                ->getMock()
            )
            ->getMock();

        $this->mockFormHelper
            ->shouldReceive('remove')
            ->with($this->mockForm, 'operator-type')
            ->getMock();

        $this->mockParams
            ->shouldReceive('fromPost')
            ->with('operator-location')
            ->andReturn(['niFlag' => 'Y'])
            ->getMock();
    }

    /**
     * Mock postSetFormData method
     */
    protected function mockPostSetFormData($data, $withBadParams = false)
    {
        $discPrefixes = [
            'AB',
            'CD'
        ];

        $discNumbering = [
            'startNumber' => 1,
            'endNumber' => 2,
            'endNumberIncreased' => 3,
            'totalPages' => 1
        ];

        $this->sm->setService(
            'Admin\Service\Data\DiscSequence',
            m::mock()
            ->shouldReceive('getDiscPrefix')
            ->with($data['discSequence'], $data['licenceType'])
            ->andReturn($data['discPrefix'])
            ->getMock()
        );

        $mockDiscStartingNumberValidator = m::mock()
            ->shouldReceive('setOriginalStartNumber')
            ->with(1)
            ->getMock();

        $this->sm->setService('goodsDiscStartNumberValidator', $mockDiscStartingNumberValidator);

        $this->mockLicenceType
            ->shouldReceive('getValue')
            ->andReturn($data['licenceType'])
            ->getMock();

        $this->mockOperatorLocation
            ->shouldReceive('getValue')
            ->andReturn($data['operatorLocation'])
            ->getMock();

        $this->mockOperatorType
            ->shouldReceive('getValue')
            ->andReturn($data['operatorType'])
            ->getMock();

        $this->mockDiscSequence
            ->shouldReceive('getValue')
            ->andReturn($data['discSequence'])
            ->shouldReceive('setValueOptions')
            ->with($discPrefixes)
            ->getMock();

        $this->mockStartNumber
            ->shouldReceive('getValue')
            ->andReturn(1)
            ->getMock();

        $this->mockEndNumber
            ->shouldReceive('setValue')
            ->andReturn(2)
            ->getMock();

        $this->mockEndNumberIncreased
            ->shouldReceive('setValue')
            ->andReturn(3)
            ->getMock();

        $this->mockTotalPages
            ->shouldReceive('setValue')
            ->andReturn(1)
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')
            ->with('prefix')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('discSequence')
                ->andReturn($this->mockDiscSequence)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('operator-location')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('niFlag')
                ->andReturn($this->mockOperatorLocation)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('licenceType')
                ->andReturn($this->mockLicenceType)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('goodsOrPsv')
                ->andReturn($this->mockOperatorType)
                ->getMock()
            )
            ->shouldReceive('has')
            ->with('operator-type')
            ->andReturn(true)
            ->once()
            ->shouldReceive('get')
            ->with('discs-numbering')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('startNumber')
                ->andReturn($this->mockStartNumber)
                ->shouldReceive('get')
                ->with('endNumber')
                ->andReturn($this->mockEndNumber)
                ->shouldReceive('get')
                ->with('endNumberIncreased')
                ->andReturn($this->mockEndNumberIncreased)
                ->shouldReceive('get')
                ->with('totalPages')
                ->andReturn($this->mockTotalPages)
                ->getMock()
            )
            ->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('discs-numbering')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('get')
                    ->with('startNumber')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('getValidatorChain')
                        ->andReturn(
                            m::mock()
                            ->shouldReceive('attach')
                            ->with($mockDiscStartingNumberValidator)
                            ->getMock()
                        )
                        ->getMock()
                    )
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->sut
            ->shouldReceive('populateDiscPrefixes')
            ->andReturn($discPrefixes)
            ->getMock();

        if (!$withBadParams) {
            $this->sut
                ->shouldReceive('processDiscNumbering')
                ->with(
                    $data['operatorLocation'],
                    $data['licenceType'],
                    $data['operatorType'],
                    $data['discPrefix'],
                    $data['discSequence'],
                    $data['startNumber']
                )
                ->once()
                ->andReturn($discNumbering);
        }
    }
}
