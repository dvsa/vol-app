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
        $this->sut = m::mock('\Olcs\Controller\Lva\AbstractTransportManagersController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetCertificates()
    {
        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $mockTmHelper->shouldReceive('getCertificateFiles')
            ->with(null)
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->sut->getCertificates());
    }

    public function testGetResponsibilityFiles()
    {
        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->sut->shouldReceive('getIdentifier')
            ->once()
            ->andReturn(111);

        $mockTmHelper->shouldReceive('getResponsibilityFiles')
            ->once()
            ->with(null, 111)
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->sut->getResponsibilityFiles());
    }

    public function testProcessCertificateUpload()
    {
        $file = ['name' => 'foo.tx'];

        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $mockTmHelper->shouldReceive('getCertificateFileData')
            ->once()
            ->with(null, $file)
            ->andReturn(['foo' => 'bar']);

        $this->sut->shouldReceive('uploadFile')
            ->once()
            ->with($file, ['foo' => 'bar'])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->processCertificateUpload($file));
    }

    public function testProcessResponsibilityFileUpload()
    {
        $file = ['name' => 'foo.tx'];

        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->sut->shouldReceive('getIdentifier')
            ->andReturn(111)
            ->shouldReceive('getLicenceId')
            ->andReturn(222);

        $mockTmHelper->shouldReceive('getResponsibilityFileData')
            ->once()
            ->with(null, $file)
            ->andReturn(['foo' => 'bar']);

        $this->sut->shouldReceive('uploadFile')
            ->once()
            ->with($file, ['foo' => 'bar', 'application' => 111, 'licence' => 222])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->processResponsibilityFileUpload($file));
    }

    public function testDetailsActionGet()
    {
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
        $mockApplication = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $mockScript->shouldReceive('loadFiles')
            ->once()
            ->with(['lva-crud', 'tm-previous-history']);

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
            ->andReturn(false)
            ->shouldReceive('remove')
            ->with($mocks['form'], 'responsibilities->tmApplicationStatus')
            ->once();

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf();

        $mockApplication->shouldReceive('getTmHeaderData')
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
        $response = $this->sut->detailsAction();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsActionPostWithAddressLookup()
    {
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
        $mockApplication = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $mockScript->shouldReceive('loadFiles')
            ->once()
            ->with(['lva-crud', 'tm-previous-history']);

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
            ->andReturn(true)
            ->shouldReceive('remove')
            ->with($mocks['form'], 'responsibilities->tmApplicationStatus')
            ->once();

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf();

        $mockApplication->shouldReceive('getTmHeaderData')
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
        $response = $this->sut->detailsAction();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsActionPostWithSubmitInvalid()
    {
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
        $mockApplication = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $mockScript->shouldReceive('loadFiles')
            ->once()
            ->with(['lva-crud', 'tm-previous-history']);

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
            ->shouldReceive('remove')
            ->with($mocks['form'], 'responsibilities->tmApplicationStatus')
            ->once();

        $mocks['form']->shouldReceive('setData')
            ->once()
            ->with($expectedFormattedData)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $mockApplication->shouldReceive('getTmHeaderData')
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
        $response = $this->sut->detailsAction();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsActionPostWithSaveInvalid()
    {
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
        $mockApplication = m::mock();
        $mockScript = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mocks = $this->expectGetDetailsForm();

        $mockScript->shouldReceive('loadFiles')
            ->once()
            ->with(['lva-crud', 'tm-previous-history']);

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
            ->with($mockInputFilter)
            ->shouldReceive('remove')
            ->with($mocks['form'], 'responsibilities->tmApplicationStatus')
            ->once();

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

        $mockApplication->shouldReceive('getTmHeaderData')
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
        $response = $this->sut->detailsAction();

        $this->assertSame($mockView, $response);
    }

    public function testDetailsActionPostWithSaveValid()
    {
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
        $mockApplication = m::mock();
        $mockFlashMessenger = m::mock();

        $mockTmDetails = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('Lva\TransportManagerDetails', $mockTmDetails);

        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
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
            ->with($mockInputFilter)
            ->shouldReceive('remove')
            ->with($mocks['form'], 'responsibilities->tmApplicationStatus')
            ->once();

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

        $mockApplication->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockTmDetails->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('lva-tm-details-save-success');

        $this->sut->shouldReceive('redirect->refresh')
            ->andReturn('REFRESH');

        // Assertions
        $response = $this->sut->detailsAction();

        $this->assertEquals('REFRESH', $response);
    }

    /**
     * @group abstractTmController
     */
    public function testDetailsActionPostWithCrudAction()
    {
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
        $mockApplication = m::mock();

        $mockTmDetails = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('Lva\TransportManagerDetails', $mockTmDetails);

        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
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
                    'add-previous-licence'

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
            ->with($mockInputFilter)
            ->shouldReceive('remove')
            ->with($mocks['form'], 'responsibilities->tmApplicationStatus')
            ->once();

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

        $mockApplication->shouldReceive('getTmHeaderData')
            ->with(333)
            ->andReturn($stubbedTmHeaderData);

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with('markup-tm-details-sub-title', ['Goods', 'AB12345678', '1234'])
            ->andReturn('TRANSLATION');

        $mockTmDetails->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        // Assertions
        $response = $this->sut->detailsAction();

        $this->assertEquals('RESPONSE', $response);
    }

    protected function expectGetDetailsForm()
    {
        // Mocks
        $mockForm = m::mock();
        $mockResponsibilitiesFieldset = m::mock();
        $mockPreviousHistoryFieldset = m::mock();
        $mockFormHelper = m::mock();
        $mockOtherLicenceTable = m::mock();
        $mockOtherLicence = m::mock();
        $mockTableBuilder = m::mock();
        $mockAoc = m::mock();
        $mockTmHelper = m::mock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\ApplicationOperatingCentre', $mockAoc);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicence);
        $this->sm->setService('Table', $mockTableBuilder);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm);

        $mockForm->shouldReceive('get')
            ->with('responsibilities')
            ->andReturn($mockResponsibilitiesFieldset)
            ->shouldReceive('get')
            ->with('previousHistory')
            ->andReturn($mockPreviousHistoryFieldset);

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
            ->with($mockPreviousHistoryFieldset, 222);

        return [
            'formHelper' => $mockFormHelper,
            'form' => $mockForm
        ];
    }

    public function testDeleteActionGet()
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

        $this->assertEquals('RESPONSE', $this->sut->deleteAction($which));
    }

    public function testDeleteOtherLicenceApplicationsAction()
    {
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

    public function testEditPreviousLicenceActionWithGet()
    {
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

    public function testDeletePreviousLicencesAction()
    {
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
}
