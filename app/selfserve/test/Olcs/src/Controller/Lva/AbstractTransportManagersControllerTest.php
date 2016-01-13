<?php

/**
 * Abstract Transport Managers Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\TransportManagerApplicationEntityService as TmaService;

/**
 * Abstract Transport Managers Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractTransportManagersControllerTest extends MockeryTestCase
{
    /**
     * @var \Olcs\Controller\Lva\AbstractTransportManagersController
     */
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->markTestSkipped();
        $this->sut = m::mock('\Olcs\Controller\Lva\AbstractTransportManagersController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    protected function setupTmaData($tma)
    {
        $this->sut->shouldReceive('handleQuery')
            ->andReturn(m::mock()->shouldReceive('getResult')->andReturn($tma)->getMock());
        $this->sut->getTmaDetails(1);
    }


    public function testGetCertificates()
    {
        $tma = [
            'transportManager' => [
                'documents' => [
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 33]],
                    ['category' => ['id' => 3], 'subCategory' => ['id' => 98]],
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 98]],
                    ['category' => ['id' => 12], 'subCategory' => ['id' => 198]],
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 98]],
                ]
            ]
        ];

        $this->setupTmaData($tma);

        $expected = [
            $tma['transportManager']['documents'][2],
            $tma['transportManager']['documents'][4],
        ];

        $this->assertEquals($expected, $this->sut->getCertificates());
    }

    public function testGetResponsibilityFiles()
    {
        $tma = [
            'application' => [
                'id' => 55,
            ],
            'transportManager' => [
                'documents' => [
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 33], 'application' => ['id' => 234]],
                    ['category' => ['id' => 3], 'subCategory' => ['id' => 98], 'application' => ['id' => 234]],
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 100], 'application' => ['id' => 234]],
                    ['category' => ['id' => 12], 'subCategory' => ['id' => 198], 'application' => ['id' => 234]],
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 100], 'application' => ['id' => 55]],
                    ['category' => ['id' => 52], 'subCategory' => ['id' => 100], 'application' => ['id' => 55]],
                    ['category' => ['id' => 5], 'subCategory' => ['id' => 100], 'application' => ['id' => 55]],
                ]
            ]
        ];

        $this->setupTmaData($tma);

        $expected = [
            $tma['transportManager']['documents'][4],
            $tma['transportManager']['documents'][6],
        ];
        $this->assertEquals($expected, $this->sut->getResponsibilityFiles());
    }

    public function testProcessCertificateUpload()
    {
        $tma = [
            'id' => 77,
            'transportManager' => [
                'id' => 44
            ]
        ];
        $this->setupTmaData($tma);

        $file = ['name' => 'foo.tx'];

        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $mockTmHelper->shouldReceive('getCertificateFileData')
            ->once()
            ->with(44, $file)
            ->andReturn(['foo' => 'bar']);

        $this->sut->shouldReceive('uploadFile')
            ->once()
            ->with($file, ['foo' => 'bar'])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->processCertificateUpload($file));
    }

    public function testProcessResponsibilityFileUpload()
    {
        $tma = [
            'id' => 77,
            'transportManager' => [
                'id' => 44
            ]
        ];
        $this->setupTmaData($tma);

        $file = ['name' => 'foo.tx'];

        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->sut->shouldReceive('getIdentifier')
            ->andReturn(111)
            ->shouldReceive('getLicenceId')
            ->andReturn(222);

        $mockTmHelper->shouldReceive('getResponsibilityFileData')
            ->once()
            ->with(44, $file)
            ->andReturn(['foo' => 'bar']);

        $this->sut->shouldReceive('uploadFile')
            ->once()
            ->with($file, ['foo' => 'bar', 'application' => 111, 'licence' => 222])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->processResponsibilityFileUpload($file));
    }

    public function testDetailsGet()
    {
        $this->markTestIncomplete();

        $stubbedTmDetails = [
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'workCd' => [
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ],
            'operatingCentres' => [
                [
                    'id' => 123
                ],
                [
                    'id' => 321
                ]
            ],
            'tmType' => [
                'id' => 'footype'
            ],
            'hoursMon' => 2,
            'hoursTue' => 3,
            'hoursWed' => 4,
            'hoursThu' => 5,
            'hoursFri' => 6,
            'hoursSat' => 0,
            'hoursSun' => 0,
            'additionalInformation' => 'Some additional info',
            'isOwner' => 'Y',
            'declarationConfirmation' => 'N'
        ];

        $expectedFormattedData = [
            'details' => [
                'emailAddress' => 'foo@bar.com',
                'birthPlace' => 'Hometown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '123 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '123 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ],
            'responsibilities' => [
                'tmType' => 'footype',
                'isOwner' => 'Y',
                'additionalInformation' => 'Some additional info',
                'operatingCentres' => [123, 321],
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 2,
                        'hoursTue' => 3,
                        'hoursWed' => 4,
                        'hoursThu' => 5,
                        'hoursFri' => 6,
                        'hoursSat' => 0,
                        'hoursSun' => 0,
                    ]
                ]
            ],
            'declarations' => [
                'confirmation' => 'N'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        // Mocks
        $mockView = m::mock();
        $mockContent = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->expectLoadScripts();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mocks['form'], ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn([])
            ->shouldReceive('isPost')
            ->andReturn(false);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(false);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf();

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockView->shouldReceive('getChildrenByCaptureTo')
            ->with('content')
            ->andReturn([$mockContent]);

        $mockContent->shouldReceive('setTemplate')
            ->with('pages/lva-tm-details');

        // Assertions
        $response = $this->sut->details();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsPostWithAddressLookup()
    {
        $this->markTestIncomplete();

        $postData = [
            'details' => [
                'birthPlace' => 'Birthtown',
                'emailAddress' => 'foo2@bar.com'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmDetails = [
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'workCd' => [
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ]
        ];

        $expectedFormattedData = [
            'details' => [
                'emailAddress' => 'foo2@bar.com',
                'birthPlace' => 'Birthtown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        // Mocks
        $mockView = m::mock();
        $mockContent = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->expectLoadScripts();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mocks['form'], ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(false);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(true);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf();

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockView->shouldReceive('getChildrenByCaptureTo')
            ->with('content')
            ->andReturn([$mockContent]);

        $mockContent->shouldReceive('setTemplate')
            ->with('pages/lva-tm-details');

        // Assertions
        $response = $this->sut->details();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsPostWithSubmitInvalid()
    {
        $this->markTestIncomplete();

        $postData = [
            'form-actions' => [
                'submit' => 1
            ],
            'details' => [
                'birthPlace' => 'Birthtown',
                'emailAddress' => 'foo2@bar.com'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmDetails = [
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'workCd' => [
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ]
        ];

        $expectedFormattedData = [
            'form-actions' => [
                'submit' => 1
            ],
            'details' => [
                'emailAddress' => 'foo2@bar.com',
                'birthPlace' => 'Birthtown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        // Mocks
        $mockView = m::mock();
        $mockContent = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->expectLoadScripts();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mocks['form'], ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(false);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockView->shouldReceive('getChildrenByCaptureTo')
            ->with('content')
            ->andReturn([$mockContent]);

        $mockContent->shouldReceive('setTemplate')
            ->with('pages/lva-tm-details');

        // Assertions
        $response = $this->sut->details();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsPostWithSaveInvalid()
    {
        $this->markTestIncomplete();

        $postData = [
            'form-actions' => [
                'save' => 1
            ],
            'details' => [
                'birthPlace' => 'Birthtown',
                'emailAddress' => 'foo2@bar.com'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmDetails = [
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'workCd' => [
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ]
        ];

        $expectedFormattedData = [
            'form-actions' => [
                'save' => 1
            ],
            'details' => [
                'emailAddress' => 'foo2@bar.com',
                'birthPlace' => 'Birthtown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        // Mocks
        $mockView = m::mock();
        $mockInputFilter = m::mock();
        $mockContent = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->expectLoadScripts();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mocks['form'], ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(false)
            ->shouldReceive('disableValidation')
            ->with($mockInputFilter);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getInputFilter')
            ->once()
            ->andReturn($mockInputFilter);

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockView->shouldReceive('getChildrenByCaptureTo')
            ->with('content')
            ->andReturn([$mockContent]);

        $mockContent->shouldReceive('setTemplate')
            ->with('pages/lva-tm-details');

        // Assertions
        $response = $this->sut->details();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsPostSubmit()
    {
        $this->markTestIncomplete();

        $postData = [
            'form-actions' => [
            ],
            'details' => [
                'birthPlace' => 'Birthtown',
                'emailAddress' => 'foo2@bar.com'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmDetails = [
            'id' => 111,
            'version' => 1,
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'version' => 1,
                'workCd' => [
                    'id' => 666,
                    'version' => 1,
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'id' => 555,
                    'version' => 1,
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'id' => 777,
                        'version' => 1,
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ]
        ];

        $expectedFormattedData = [
            'form-actions' => [
            ],
            'details' => [
                'emailAddress' => 'foo2@bar.com',
                'birthPlace' => 'Birthtown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        $expectedParams = [
            'submit' => true,
            'transportManagerApplication' => [
                'id' => 111,
                'version' => 1
            ],
            'transportManager' => [
                'id' => 222,
                'version' => 1
            ],
            'contactDetails' => [
                'id' => 555,
                'version' => 1
            ],
            'workContactDetails' => [
                'id' => 666,
                'version' => 1,
            ],
            'person' => [
                'id' => 777,
                'version' => 1
            ],
            'data' => $expectedFormattedData
        ];

        // Mocks
        $mockInputFilter = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();
        $mockFlashMessenger = m::mock();

        $mockTmDetails = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('Lva\TransportManagerDetails', $mockTmDetails);

        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(false)
            ->shouldReceive('disableValidation')
            ->with($mockInputFilter);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->once()
            ->andReturn($expectedFormattedData);

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockTmDetails->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva-tm-details-submit-success');

        $this->sut->shouldReceive('redirect->refresh')
            ->andReturn('REFRESH');

        // Assertions
        $response = $this->sut->details();

        $this->assertEquals('REFRESH', $response);
    }

    public function testDetailsPostSave()
    {
        $this->markTestIncomplete();

        $postData = [
            'form-actions' => [
                'save' => 1
            ],
            'details' => [
                'birthPlace' => 'Birthtown',
                'emailAddress' => 'foo2@bar.com'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmDetails = [
            'id' => 111,
            'version' => 1,
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'version' => 1,
                'workCd' => [
                    'id' => 666,
                    'version' => 1,
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'id' => 555,
                    'version' => 1,
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'id' => 777,
                        'version' => 1,
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ]
        ];

        $expectedFormattedData = [
            'form-actions' => [
                'save' => 1
            ],
            'details' => [
                'emailAddress' => 'foo2@bar.com',
                'birthPlace' => 'Birthtown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        $expectedParams = [
            'submit' => false,
            'transportManagerApplication' => [
                'id' => 111,
                'version' => 1
            ],
            'transportManager' => [
                'id' => 222,
                'version' => 1
            ],
            'contactDetails' => [
                'id' => 555,
                'version' => 1
            ],
            'workContactDetails' => [
                'id' => 666,
                'version' => 1,
            ],
            'person' => [
                'id' => 777,
                'version' => 1
            ],
            'data' => $expectedFormattedData
        ];

        // Mocks
        $mockInputFilter = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();
        $mockFlashMessenger = m::mock();

        $mockTmDetails = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('Lva\TransportManagerDetails', $mockTmDetails);

        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(false)
            ->shouldReceive('disableValidation')
            ->with($mockInputFilter);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getInputFilter')
            ->once()
            ->andReturn($mockInputFilter)
            ->shouldReceive('getData')
            ->once()
            ->andReturn($expectedFormattedData);

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockTmDetails->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        $this->sut->shouldReceive('redirectTmToHome')
            ->with()
            ->once()
            ->andReturn('RESPONSE');

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva-tm-details-save-success');

        // Assertions
        $response = $this->sut->details();

        $this->assertEquals('RESPONSE', $response);
    }

    /**
     * @group abstractTmController
     */
    public function testDetailsPostWithCrudAction()
    {
        $this->markTestIncomplete();

        $postData = [
            'table' => [
                'action' => 'foo'
            ],
            'details' => [
                'birthPlace' => 'Birthtown',
                'emailAddress' => 'foo2@bar.com'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmDetails = [
            'id' => 111,
            'version' => 1,
            'application' => [
                'id' => 333
            ],
            'transportManager' => [
                'id' => 222,
                'version' => 1,
                'workCd' => [
                    'id' => 666,
                    'version' => 1,
                    'address' => [
                        'addressLine1' => '123 work street',
                        'town' => 'worktown',
                        'postcode' => 'WO1 1RK'
                    ],
                ],
                'homeCd' => [
                    'id' => 555,
                    'version' => 1,
                    'address' => [
                        'addressLine1' => '123 home street',
                        'town' => 'hometown',
                        'postcode' => 'HO1 1ME'
                    ],
                    'emailAddress' => 'foo@bar.com',
                    'person' => [
                        'id' => 777,
                        'version' => 1,
                        'forename' => 'Foo',
                        'familyName' => 'Bar',
                        'birthPlace' => 'Hometown',
                        'birthDate' => '1989-08-23'
                    ]
                ]
            ]
        ];

        $expectedFormattedData = [
            'table' => [
                'action' => 'foo'
            ],
            'details' => [
                'emailAddress' => 'foo2@bar.com',
                'birthPlace' => 'Birthtown',
                'name' => 'Foo Bar',
                'birthDate' => '23/08/1989'
            ],
            'homeAddress' => [
                'addressLine1' => '321 home street',
                'town' => 'hometown',
                'postcode' => 'HO1 1ME'
            ],
            'workAddress' => [
                'addressLine1' => '321 work street',
                'town' => 'worktown',
                'postcode' => 'WO1 1RK'
            ]
        ];

        $stubbedTmHeaderData = [
            'goodsOrPsv' => [
                'description' => 'Goods'
            ],
            'licence' => [
                'licNo' => 'AB12345678'
            ],
            'id' => '1234'
        ];

        $expectedParams = [
            'submit' => false,
            'transportManagerApplication' => [
                'id' => 111,
                'version' => 1
            ],
            'transportManager' => [
                'id' => 222,
                'version' => 1
            ],
            'contactDetails' => [
                'id' => 555,
                'version' => 1
            ],
            'workContactDetails' => [
                'id' => 666,
                'version' => 1,
            ],
            'person' => [
                'id' => 777,
                'version' => 1
            ],
            'data' => $expectedFormattedData
        ];

        // Mocks
        $mockInputFilter = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockTranslationHelper = m::mock();

        $mockTmDetails = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('Lva\TransportManagerDetails', $mockTmDetails);

        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('getCrudAction')
            ->with([['action' => 'foo']])
            ->andReturn('CRUD')
            ->shouldReceive('handleCrudAction')
            ->once()
            ->with(
                'CRUD',
                [
                    'add-other-licence-applications',
                    'add-previous-conviction',
                    'add-previous-licence',
                    'add-employment'
                ],
                'grand_child_id',
                'lva-application/transport_manager_details/action'
            )
            ->andReturn('RESPONSE');

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mocks['formHelper']->shouldReceive('processAddressLookupForm')
            ->with($mocks['form'], $mockRequest)
            ->andReturn(false)
            ->shouldReceive('disableValidation')
            ->with($mockInputFilter);

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getInputFilter')
            ->once()
            ->andReturn($mockInputFilter)
            ->shouldReceive('getData')
            ->once()
            ->andReturn($expectedFormattedData);

        $mocks['applicationEntity']->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockTmDetails->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        // Assertions
        $response = $this->sut->details();

        $this->assertEquals('RESPONSE', $response);
    }

    protected function expectGetDetailsForm()
    {
        // Mocks
        $mockForm = m::mock();
        $mockResponsibilitiesFieldset = m::mock();
        $mockPreviousHistoryFieldset = m::mock();
        $mockOtherEmployment = m::mock();
        $mockFormHelper = m::mock();
        $mockOtherLicenceTable = m::mock();
        $mockOtherLicence = m::mock();
        $mockTableBuilder = m::mock();
        $mockAoc = m::mock();
        $mockTmHelper = m::mock();
        $mockApplication = m::mock();
        $mockDeclarations = m::mock();

        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAoc);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);
        $this->sm->setService('Table', $mockTableBuilder);
        $this->sm->setService('Entity\Application', $mockApplication);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'responsibilities->tmApplicationStatus');

        $mockForm->shouldReceive('get')
            ->with('responsibilities')
            ->andReturn($mockResponsibilitiesFieldset)
            ->shouldReceive('get')
            ->with('previousHistory')
            ->andReturn($mockPreviousHistoryFieldset)
            ->shouldReceive('get')
            ->with('otherEmployment')
            ->andReturn($mockOtherEmployment)
            ->shouldReceive('get')
            ->with('declarations')
            ->andReturn($mockDeclarations);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('tm.otherlicences-applications', ['table' => 'data'])
            ->andReturn($mockOtherLicenceTable);

        $mockOtherLicence->shouldReceive('getByTmApplicationId')
            ->with(111)
            ->andReturn(['table' => 'data']);

        $mockAoc->shouldReceive('getForSelect')
            ->with(333)
            ->andReturn(['foo' => 'bar']);

        $mockTmHelper->shouldReceive('alterResponsibilitiesFieldset')
            ->with($mockResponsibilitiesFieldset, ['foo' => 'bar'], $mockOtherLicenceTable)
            ->shouldReceive('alterPreviousHistoryFieldset')
            ->with($mockPreviousHistoryFieldset, 222)
            ->shouldReceive('prepareOtherEmploymentTable')
            ->with($mockOtherEmployment, 222);

        $mockApplication->shouldReceive('getTypeOfLicenceData')
            ->once()
            ->with(333)
            ->andReturn(['niFlag' => 'Y']);

        $mockPreviousHistoryFieldset->shouldReceive('get->get->getTable->setEmptyMessage')
            ->with('transport-manager.convictionsandpenalties.table.empty.ni');

        $mockDeclarations->shouldReceive('get')
            ->with('internal')
            ->andReturn(
                m::mock()
                ->shouldReceive('setValue')
                ->once()
                ->with('markup-tm-declaration-ni-internal')
                ->getMock()
            )->shouldReceive('get')
            ->with('external')
            ->andReturn(
                m::mock()
                ->shouldReceive('setValue')
                ->once()
                ->with('markup-tm-declaration-ni-external')
                ->getMock()
            )->shouldReceive('get')
            ->with('confirmation')
            ->andReturn(
                m::mock()
                ->shouldReceive('setLabel')
                ->once()
                ->with('markup-tm-declaration-ni-confirmation')
                ->getMock()
            );

        return [
            'formHelper' => $mockFormHelper,
            'form' => $mockForm,
            'applicationEntity' => $mockApplication
        ];
    }

    public function testGenericDeleteGet()
    {
        $which = 'Foo';

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->with('GenericDeleteConfirmation', $mockRequest)
            ->andReturn($mockForm);

        $this->sut->shouldReceive('render')
            ->with('delete', $mockForm, ['sectionText' => 'delete.confirmation.text'])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->genericDelete($which));
    }

    public function testDeleteOtherLicenceApplicationsAction()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFlashMessenger = m::mock();
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockDeleteOtherLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\DeleteOtherLicence', $mockDeleteOtherLicence);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn('111,222');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('transport_managers-details-OtherLicences-delete-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockDeleteOtherLicence->shouldReceive('process')
            ->once()
            ->with(['ids' => [111, 222]]);

        $this->assertEquals('RESPONSE', $this->sut->deleteOtherLicenceApplicationsAction());
    }

    public function testAddOtherLicenceApplicationsActionWithCancelButtonPressed()
    {
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->once()
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $response = $this->sut->addOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditOtherLicenceApplicationsActionWithCancelButtonPressed()
    {
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->once()
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $response = $this->sut->editOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddOtherLicenceApplicationsActionWithGet()
    {
        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-add-OtherLicences', $mockForm)
            ->andReturn('RESPONSE');

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        // Assertions
        $response = $this->sut->addOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditOtherLicenceApplicationsActionWithGet()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockOtherLicence = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-edit-OtherLicences', $mockForm)
            ->andReturn('RESPONSE')
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockOtherLicence->shouldReceive('getById')
            ->once()
            ->with(111)
            ->andReturn(['foo' => 'bar']);

        $mockForm->shouldReceive('setData')
            ->with(['data' => ['foo' => 'bar']]);

        // Assertions
        $response = $this->sut->editOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddOtherLicenceApplicationsActionWithPostInvalid()
    {
        $postData = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-add-OtherLicences', $mockForm)
            ->andReturn('RESPONSE');

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with(['data' => ['foo' => 'bar']])
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        // Assertions
        $response = $this->sut->addOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditOtherLicenceApplicationsActionWithPostInvalid()
    {
        $postData = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-edit-OtherLicences', $mockForm)
            ->andReturn('RESPONSE')
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with(['data' => ['foo' => 'bar']])
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        // Assertions
        $response = $this->sut->editOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddOtherLicenceApplicationsActionWithPostValid()
    {
        $this->markTestIncomplete();

        $postData = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $expectedParams = [
            'data' => [
                'foo' => 'bar',
                'transportManagerApplication' => 222
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockOtherLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockFlashMessenger = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\OtherLicence', $mockOtherLicence);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(222);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockOtherLicence->shouldReceive('process')
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva.section.title.transport_managers-details-OtherLicences-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        // Assertions
        $response = $this->sut->addOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditOtherLicenceApplicationsActionWithPostValid()
    {
        $this->markTestIncomplete();

        $postData = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $expectedParams = [
            'data' => [
                'foo' => 'bar',
                'transportManagerApplication' => 222
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockOtherLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockFlashMessenger = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\OtherLicence', $mockOtherLicence);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(222)
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockOtherLicence->shouldReceive('process')
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva.section.title.transport_managers-details-OtherLicences-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        // Assertions
        $response = $this->sut->editOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddOtherLicenceApplicationsActionWithPostValidAddAnother()
    {
        $this->markTestIncomplete();

        $postData = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $expectedParams = [
            'data' => [
                'foo' => 'bar',
                'transportManagerApplication' => 222
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockOtherLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockFlashMessenger = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\OtherLicence', $mockOtherLicence);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(true)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(222);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\TmOtherLicence', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockOtherLicence->shouldReceive('process')
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva.section.title.transport_managers-details-OtherLicences-success');

        $this->sut->shouldReceive('redirect->refresh')
            ->andReturn('RESPONSE');

        // Assertions
        $response = $this->sut->addOtherLicenceApplicationsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testCheckForRedirectWithoutCancel()
    {
        $lvaId = 111;

        $this->sut->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false);

        $response = $this->sut->checkForRedirect($lvaId);

        $this->assertNull($response);
    }

    public function testCheckForRedirectWithDetails()
    {
        $lvaId = 111;

        $this->sut->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('details')
            ->shouldReceive('handleCancelRedirect')
            ->with($lvaId)
            ->andReturn('RESPONSE');

        $response = $this->sut->checkForRedirect($lvaId);

        $this->assertEquals('RESPONSE', $response);
    }

    public function testCheckForRedirectWithoutDetails()
    {
        $lvaId = 111;

        $this->sut->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('add')
            ->shouldReceive('backToDetails')
            ->andReturn('RESPONSE');

        $response = $this->sut->checkForRedirect($lvaId);

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddPreviousLicenceActionWithPostValid()
    {
        $this->markTestIncomplete();

        $postData = [
            'tm-previous-licences-details' => [
                'foo' => 'bar'
            ]
        ];

        $expectedParams = [
            'data' => [
                'foo' => 'bar',
                'transportManager' => 333
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockTma = m::mock();
        $mockPreviousLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockFlashMessenger = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\OtherLicence', $mockPreviousLicence);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(222);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('TmPreviousLicences', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockPreviousLicence->shouldReceive('process')
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva.section.title.transport_managers-details-PreviousLicences-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockTma->shouldReceive('getTransportManagerId')
            ->with(222)
            ->andReturn(333);

        // Assertions
        $response = $this->sut->addPreviousLicenceAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddPreviousConvictionActionWithPostValid()
    {
        $this->markTestIncomplete();

        $postData = [
            'tm-convictions-and-penalties-details' => [
                'foo' => 'bar'
            ]
        ];

        $expectedParams = [
            'data' => [
                'foo' => 'bar',
                'transportManager' => 333
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockTma = m::mock();
        $mockPreviousLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockFlashMessenger = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\PreviousConviction', $mockPreviousLicence);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(222);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('TmConvictionsAndPenalties', $mockRequest)
            ->andReturn($mockForm);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockPreviousLicence->shouldReceive('process')
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva.section.title.transport_managers-details-PreviousConvictions-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockTma->shouldReceive('getTransportManagerId')
            ->with(222)
            ->andReturn(333);

        // Assertions
        $response = $this->sut->addPreviousConvictionAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testAddEmploymentActionWithPostValid()
    {
        $this->markTestIncomplete();

        $postData = [
            'tm-employer-name-details' => [
                'employerName' => 'Foo ltd'
            ],
            'address' => [
                'addressLine1' => 'Foo street'
            ],
            'tm-employment-details' => [
                'foo' => 'bar'
            ]
        ];

        $expectedParams = [
            'address' => [
                'addressLine1' => 'Foo street'
            ],
            'data' => [
                'foo' => 'bar',
                'employerName' => 'Foo ltd',
                'transportManager' => 333
            ]
        ];

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockTma = m::mock();
        $mockTmEmployment = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockFlashMessenger = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('TmEmployment', $mockTmEmployment);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(222);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('TmEmployment', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(false);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockForm->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $mockTmEmployment->shouldReceive('process')
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva.section.title.transport_managers-details-OtherEmployments-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockTma->shouldReceive('getTransportManagerId')
            ->with(222)
            ->andReturn(333);

        // Assertions
        $response = $this->sut->addEmploymentAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditPreviousLicenceActionWithGet()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockOtherLicence = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-edit-PreviousLicences', $mockForm)
            ->andReturn('RESPONSE')
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('TmPreviousLicences', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockOtherLicence->shouldReceive('getById')
            ->once()
            ->with(111)
            ->andReturn(['foo' => 'bar']);

        $mockForm->shouldReceive('setData')
            ->with(['tm-previous-licences-details' => ['foo' => 'bar']]);

        // Assertions
        $response = $this->sut->editPreviousLicenceAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditPreviousConvictionActionWithGet()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockPreviousConviction = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\PreviousConviction', $mockPreviousConviction);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-edit-PreviousConvictions', $mockForm)
            ->andReturn('RESPONSE')
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('TmConvictionsAndPenalties', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockPreviousConviction->shouldReceive('getById')
            ->once()
            ->with(111)
            ->andReturn(['foo' => 'bar']);

        $mockForm->shouldReceive('setData')
            ->with(['tm-convictions-and-penalties-details' => ['foo' => 'bar']]);

        // Assertions
        $response = $this->sut->editPreviousConvictionAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testEditEmploymentActionWithGet()
    {
        // Mocks
        $mockRequest = m::mock();
        $mockFormHelper = m::mock();
        $mockForm = m::mock();
        $mockTmHelper = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        // Expectations
        $this->sut->shouldReceive('isButtonPressed')
            ->once()
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with('transport_managers-details-edit-OtherEmployments', $mockForm)
            ->andReturn('RESPONSE')
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn(111);

        $mockFormHelper->shouldReceive('createFormWithRequest')
            ->once()
            ->with('TmEmployment', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother')
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(false);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(false);

        $mockForm->shouldReceive('setData')
            ->with(['foo' => 'bar']);

        $mockTmHelper->shouldReceive('getOtherEmploymentData')
            ->with(111)
            ->andReturn(['foo' => 'bar']);

        // Assertions
        $response = $this->sut->editEmploymentAction();

        $this->assertEquals('RESPONSE', $response);
    }

    public function testDeletePreviousLicencesAction()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFlashMessenger = m::mock();
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockDeleteOtherLicence = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\DeleteOtherLicence', $mockDeleteOtherLicence);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn('111,222');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('transport_managers-details-PreviousLicences-delete-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockDeleteOtherLicence->shouldReceive('process')
            ->once()
            ->with(['ids' => [111, 222]]);

        $this->assertEquals('RESPONSE', $this->sut->deletePreviousLicenceAction());
    }

    public function testDeletePreviousConvictionsAction()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFlashMessenger = m::mock();
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockDeletePreviousConviction = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\DeletePreviousConviction', $mockDeletePreviousConviction);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn('111,222');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('transport_managers-details-PreviousConvictions-delete-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockDeletePreviousConviction->shouldReceive('process')
            ->once()
            ->with(['ids' => [111, 222]]);

        $this->assertEquals('RESPONSE', $this->sut->deletePreviousConvictionAction());
    }

    public function testDeleteEmploymentAction()
    {
        $this->markTestIncomplete();

        // Mocks
        $mockRequest = m::mock();
        $mockFlashMessenger = m::mock();
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $mockDeleteOtherEmployment = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $this->sm->setService('BusinessServiceManager', $bsm);
        $bsm->setService('Lva\DeleteOtherEmployment', $mockDeleteOtherEmployment);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('grand_child_id')
            ->andReturn('111,222');

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('transport_managers-details-OtherEmployments-delete-success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->andReturn('RESPONSE');

        $mockDeleteOtherEmployment->shouldReceive('process')
            ->once()
            ->with(['ids' => [111, 222]]);

        $this->assertEquals('RESPONSE', $this->sut->deleteEmploymentAction());
    }

    protected function expectLoadScripts()
    {
        $mockScript = m::mock();
        $this->sm->setService('Script', $mockScript);

        $mockScript->shouldReceive('loadFiles')
            ->once()
            ->with(['lva-crud', 'tm-previous-history', 'tm-other-employment', 'tm-details']);
    }

    public function testEditActionShowConfirmation()
    {
        $mockHelperForm = m::mock();
        $this->sm->setService('Helper\Form', $mockHelperForm);

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest);

        $mockRequest->shouldReceive('isPost')->once()->andReturn(false);
        $mockHelperForm->shouldReceive('createForm')
            ->with('GenericConfirmation')
            ->once()
            ->andReturn('FORM');
        $mockHelperForm->shouldReceive('setFormActionFromRequest')
            ->with('FORM', $mockRequest)
            ->once()
            ->andReturn('FORM');

        $this->sut->shouldReceive('render')
            ->with(
                'transport-manager-application.edit-form',
                'FORM',
                ['sectionText' => 'transport-manager-application.edit-form.confirmation']
            )->once()
            ->andReturn('VIEW');

        $this->assertEquals('VIEW', $this->sut->editAction());
    }

    public function testEditActionPost()
    {
        $this->markTestIncomplete();

        $mockHelperForm = m::mock();
        $this->sm->setService('Helper\Form', $mockHelperForm);

        $mockTmaEntityService = m::mock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTmaEntityService);

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest);

        $mockRequest->shouldReceive('isPost')->once()->andReturn(true);
        $mockHelperForm->shouldReceive('createForm')
            ->with('GenericConfirmation')
            ->once()
            ->andReturn('FORM');
        $mockHelperForm->shouldReceive('setFormActionFromRequest')
            ->with('FORM', $mockRequest)
            ->once()
            ->andReturn('FORM');

        $this->sut->shouldReceive('params')
            ->with('child_id')
            ->once()
            ->andReturn(54);

        $mockTmaEntityService->shouldReceive('updateStatus')
            ->with(54, TmaService::STATUS_INCOMPLETE);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/transport_manager_details', [], [], true)
            ->once()
            ->andReturn('VIEW');

        $this->assertEquals('VIEW', $this->sut->editAction());
    }

    protected function setupDetailsAction($query, $userTmId, $tmaStatus)
    {
        $mockUserEntityService = m::mock();
        $this->sm->setService('Entity\User', $mockUserEntityService);

        $mockTmaEntityService = m::mock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTmaEntityService);

        $tmaData = [
            'transportManager' => [
                'id' => 43,
                'homeCd' => [
                    'person' => [
                        'forename' => 'Billy',
                        'familyName' => 'Smith',
                    ]
                ],
            ],
            'tmApplicationStatus' => [
                'id' => $tmaStatus
            ],
            'application' => [
                'id' => 755,
                'licence' => [
                    'licNo' => 'LIC001'
                ]
            ]
        ];
        $userData = [
            'transportManager' => [
                'id' => $userTmId,
                'homeCd' => [
                    'person' => [
                        'forename' => 'Billy',
                        'familyName' => 'Smith',
                    ]
                ],
            ],
        ];

        $this->sut->shouldReceive('params')
            ->with('child_id')
            ->once()
            ->andReturn(154);
        $this->sut->shouldReceive('getRequest->getQuery')
            ->andReturn($query);

        $mockTmaEntityService->shouldReceive('getContactApplicationDetails')
            ->with(154)
            ->once()
            ->andReturn($tmaData);

        $mockUserEntityService->shouldReceive('getCurrentUserId')->once()->andReturn(22);
        $mockUserEntityService->shouldReceive('getUserDetails')->with(22)->once()->andReturn($userData);

        $mockUrlHelper = m::mock();

        $this->sut->shouldReceive('url')->andReturn($mockUrlHelper);

        $mockUrlHelper->shouldReceive('fromRoute')
            ->with('transport_manager_review', ['id' => 154])
            ->once()
            ->andReturn('A-URL');

        $mockUrlHelper->shouldReceive('fromRoute')
            ->with('lva-application/transport_manager_details/action', ['action' => 'edit'], [], true)
            ->once()
            ->andReturn('EDIT-URL');

        return $mockTmaEntityService;
    }


    public function dataProviderDetailsAction()
    {
        return [
            // userId, tmaStatus, markup, translateReplace, progress, view, edit
            'POSTAL TM' => [43, TmaService::STATUS_POSTAL_APPLICATION, 'markup-tma-1', null, false, false],
            'POSTAL NON TM' => [143, TmaService::STATUS_POSTAL_APPLICATION, 'markup-tma-1', null, false, false],
            'INCOMPLETE NON TM' => [143, TmaService::STATUS_INCOMPLETE, 'markup-tma-3', 0, false, false],
            'AW SIG NON TM' => [43, TmaService::STATUS_AWAITING_SIGNATURE, 'markup-tma-4', 1, true, true],
            'AW SIG TM' => [143, TmaService::STATUS_AWAITING_SIGNATURE, 'markup-tma-5', 1, true, false],
            'TM SIG TM' => [43, TmaService::STATUS_TM_SIGNED, 'markup-tma-6', 2, true, true],
            'TM SIG NON TM' => [143, TmaService::STATUS_TM_SIGNED, 'markup-tma-7', 2, true, false],
            'OP SIG TM' => [43, TmaService::STATUS_OPERATOR_SIGNED, 'markup-tma-8', 3, true, false],
            'OP SIG NON TM' => [143, TmaService::STATUS_OPERATOR_SIGNED, 'markup-tma-8', 3, true, false],
            'REC TM' => [43, TmaService::STATUS_RECEIVED, 'markup-tma-9', 3, true, false],
            'REC NON TM' => [143, TmaService::STATUS_RECEIVED, 'markup-tma-9', 3, true, false],
        ];
    }

    /**
     * @dataProvider dataProviderDetailsAction
     */
    public function testDetailsAction(
        $userTmId,
        $tmaStatus,
        $translationFile,
        $progress,
        $viewAction,
        $editAction
    ) {
        $this->markTestIncomplete();

        $this->setupDetailsAction(false, $userTmId, $tmaStatus);

        $mockHelperTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockHelperTranslator);

        switch ($translationFile) {
            case 'markup-tma-4':
                $mockHelperTranslator->shouldReceive('translateReplace')
                    ->with($translationFile, ['A-URL', 'EDIT-URL'])
                    ->once()
                    ->andReturn('HTML');
                break;
            case 'markup-tma-6':
            case 'markup-tma-7':
                $mockHelperTranslator->shouldReceive('translateReplace')
                    ->with($translationFile, ['A-URL'])
                    ->once()
                    ->andReturn('HTML');
                break;
            default:
                $mockHelperTranslator->shouldReceive('translate')
                    ->with($translationFile)
                    ->once()
                    ->andReturn('HTML');
        }

        $view = $this->sut->detailsAction();

        $this->assertEquals('pages/lva-tm-details-action', $view->getTemplate());
        $this->assertEquals($progress, $view->getVariable('progress'));
        $this->assertEquals(
            ['id' => $tmaStatus],
            $view->getVariable('tmaStatus')
        );
        $this->assertEquals('HTML', $view->getVariable('content'));
        $this->assertEquals(['view' => $viewAction, 'edit' => $editAction], $view->getVariable('actions'));
        $this->assertEquals('A-URL', $view->getVariable('viewActionUrl'));
        $this->assertEquals(43, $view->getVariable('referenceNo'));
        $this->assertEquals('LIC001/755', $view->getVariable('licenceApplicationNo'));
        $this->assertEquals('Billy Smith', $view->getVariable('tmFullName'));
        $this->assertEquals($userTmId == 43, $view->getVariable('userIsThisTransportManager'));
    }

    public function testDetailsActionTmIncomplete()
    {
        $this->markTestIncomplete();

        $this->setupDetailsAction(false, 43, TmaService::STATUS_INCOMPLETE);

        $mockHelperTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockHelperTranslator);

        $this->sut->shouldReceive('details')
            ->once()
            ->andReturn('VIEW');

        $this->assertEquals('VIEW', $this->sut->detailsAction());
    }

    public function testDetailsActionUpdateOpSigned()
    {
        $this->markTestIncomplete();

        $mockTmaEntityService = $this->setupDetailsAction('opsigned', 43, TmaService::STATUS_POSTAL_APPLICATION);

        $mockHelperTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockHelperTranslator);
        $mockHelperTranslator->shouldReceive('translate');

        $mockTmaEntityService->shouldReceive('updateStatus')
            ->with(154, TmaService::STATUS_OPERATOR_SIGNED)
            ->once();

        $this->sut->detailsAction();
    }

    public function testDetailsActionUpdateTmSigned()
    {
        $this->markTestIncomplete();

        $mockTmaEntityService = $this->setupDetailsAction('tmsigned', 43, TmaService::STATUS_POSTAL_APPLICATION);

        $mockHelperTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockHelperTranslator);
        $mockHelperTranslator->shouldReceive('translate');

        $mockTmaEntityService->shouldReceive('updateStatus')
            ->with(154, TmaService::STATUS_TM_SIGNED)
            ->once();

        $this->sut->detailsAction();
    }

    public function testReviewAction()
    {
        $view = $this->sut->reviewAction();

        $this->assertEquals('pages/placeholder', $view->getTemplate());
    }

    public function dataProviderEditDetailsActionPreGranted()
    {
        return [
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_NOT_SUBMITTED],
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
        ];
    }

    /**
     * @dataProvider dataProviderEditDetailsActionPreGranted
     */
    public function testEditDetailsActionPreGranted($status)
    {
        $this->markTestIncomplete();

        $mockTma = m::mock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);

        $tmaData = [
            'application' => [
                'status' => [
                    'id' => $status
                ]
            ]
        ];

        $this->sut->shouldReceive('params')->with('child_id')->once()->andReturn(43);

        $mockTma->shouldReceive('getTransportManagerApplication')->with(43)->once()->andReturn($tmaData);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with("lva-application/transport_manager_details", [], [], true)
            ->once()
            ->andReturn('VIEW');

        $this->assertEquals('VIEW', $this->sut->editDetailsAction());
    }

    public function dataProviderEditDetailsActionNotPreGranted()
    {
        return [
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_GRANTED],
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_NOT_TAKEN_UP],
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_REFUSED],
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_VALID],
            [\Common\Service\Entity\ApplicationEntityService::APPLICATION_STATUS_WITHDRAWN],
        ];
    }

    /**
     * @dataProvider dataProviderEditDetailsActionNotPreGranted
     */
    public function testEditDetailsActionNotPreGranted($status)
    {
        $this->markTestIncomplete();

        $mockTma = m::mock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);

        $tmaData = [
            'application' => [
                'status' => [
                    'id' => $status
                ]
            ]
        ];

        $this->sut->shouldReceive('params')->with('child_id')->once()->andReturn(43);

        $mockTma->shouldReceive('getTransportManagerApplication')->with(43)->once()->andReturn($tmaData);

        $view = $this->sut->editDetailsAction();

        $this->assertEquals('markup-tma-edit-error', $view->getVariable('translateMessage'));
    }

    public function dataProviderTestRedirectTmToHome()
    {
        return [
            'Has both perms' => [true, true, true],
            'Has only TM perm' => [false, true, false],
            'Has only LVA perm' => [true, false, true],
            'Has no perms - NIT POSSIBLE' => [true, false, false],
        ];
    }

    /**
     * @dataProvider dataProviderTestRedirectTmToHome
     */
    public function testRedirectTmToHome($gotoTmPage, $permissionTmDashboard, $permissionLva)
    {
        $this->sut->shouldReceive('isGranted')
            ->with('selfserve-tm-dashboard')
            ->once()
            ->andReturn($permissionTmDashboard);

        if ($permissionTmDashboard) {
            $this->sut->shouldReceive('isGranted')
                ->with('selfserve-lva')
                ->once()
                ->andReturn($permissionLva);
        }

        if ($gotoTmPage) {
            $this->sut->shouldReceive('getIdentifier')
                ->with()
                ->once()
                ->andReturn(1966);

            $this->sut->shouldReceive('redirect->toRoute')
                ->with('lva-application/transport_managers', ['application' => 1966], [], false)
                ->once()
                ->andReturn('RESPONSE');
        } else {
            $this->sut->shouldReceive('redirect->toRoute')
                ->with('dashboard')
                ->once()
                ->andReturn('RESPONSE');
        }

        $this->assertEquals('RESPONSE', $this->sut->redirectTmToHome());
    }
}
