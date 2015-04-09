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

    public function testDetailsActionGet()
    {
        $stubbedTmDetails = [
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
        $mockForm = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockFormHelper = m::mock();
        $mockTranslationHelper = m::mock();
        $mockApplication = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mockForm, ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn([])
            ->shouldReceive('isPost')
            ->andReturn(false);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm)
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(false);

        $mockForm->shouldReceive('setData')
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
        $mockForm = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockFormHelper = m::mock();
        $mockTranslationHelper = m::mock();
        $mockApplication = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mockForm, ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(false);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm)
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(true);

        $mockForm->shouldReceive('setData')
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
        $mockForm = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockFormHelper = m::mock();
        $mockTranslationHelper = m::mock();
        $mockApplication = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mockForm, ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm)
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(false);

        $mockForm->shouldReceive('setData')
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
        $mockForm = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockFormHelper = m::mock();
        $mockTranslationHelper = m::mock();
        $mockApplication = m::mock();

        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getIdentifier')
            ->andReturn(333)
            ->shouldReceive('render')
            ->with('transport_managers-details', $mockForm, ['subTitle' => 'TRANSLATION'])
            ->andReturn($mockView);

        $mockRequest->shouldReceive('getPost')
            ->andReturn($postData)
            ->shouldReceive('isPost')
            ->andReturn(true);

        $mockTma->shouldReceive('getTransportManagerDetails')
            ->once()
            ->with(111)
            ->andReturn($stubbedTmDetails);

        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm)
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(false)
            ->shouldReceive('disableValidation')
            ->with($mockInputFilter);

        $mockForm->shouldReceive('setData')
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
        $mockForm = m::mock();
        $mockRequest = m::mock();
        $mockTma = m::mock();
        $mockFormHelper = m::mock();
        $mockTranslationHelper = m::mock();
        $mockApplication = m::mock();
        $mockFlashMessenger = m::mock();

        $mockTmDetails = m::mock('\Common\BusinessService\BusinessServiceInterface');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('Lva\TransportManagerDetails', $mockTmDetails);

        $this->sm->setService('BusinessServiceManager', $bsm);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTma);
        $this->sm->setService('Entity\Application', $mockApplication);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('child_id')
            ->andReturn(111)
            ->shouldReceive('processFiles')
            ->once()
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

        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\TransportManagerDetails')
            ->andReturn($mockForm)
            ->shouldReceive('processAddressLookupForm')
            ->with($mockForm, $mockRequest)
            ->andReturn(false)
            ->shouldReceive('disableValidation')
            ->with($mockInputFilter);

        $mockForm->shouldReceive('setData')
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
}
