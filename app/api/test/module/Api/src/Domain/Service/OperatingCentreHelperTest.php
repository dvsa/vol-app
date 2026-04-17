<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\CreateOperatingCentre;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper;
use Laminas\ServiceManager\ServiceLocatorInterface;

class OperatingCentreHelperTest extends MockeryTestCase
{
    protected OperatingCentreHelper $sut;

    protected m\MockInterfac|AddressHelperService $addressService;

    protected m\MockInterface|DocumentRepository $documentRepo;

    protected m\MockInterface|TrafficAreaValidator $trafficAreaValidator;

    public function setUp(): void
    {
        $this->addressService = m::mock(AddressHelperService::class);
        $this->documentRepo = m::mock(DocumentRepository::class);
        $this->trafficAreaValidator = m::mock(TrafficAreaValidator::class);

        $sm = m::mock(ServiceLocatorInterface::class);
        $sm->allows('get')
            ->with(AddressHelperService::class)
            ->andReturn($this->addressService);

        $sm->allows('get')
            ->with('RepositoryServiceManager')
            ->andReturnSelf()
            ->getMock()
            ->allows('get')
            ->with('Document')
            ->andReturn($this->documentRepo)
            ->getMock()
            ->allows('get')
            ->with('TrafficAreaValidator')
            ->andReturn($this->trafficAreaValidator);

        $this->sut = new OperatingCentreHelper();
        $this->sut->__invoke($sm, null);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validateWithErrors')]
    public function testValidateWithErrorsInternal(bool $isPsv, bool $isRestricted, array $commandData, array $expected): void
    {
        $entity = m::mock();
        $entity->allows('isPsv')->andReturn($isPsv);
        $entity->allows('isRestricted')->andReturn($isRestricted);
        $entity->allows('isGoods')->andReturn(!$isPsv);

        $command = CreateOperatingCentre::create($commandData);

        $docCollection = new ArrayCollection();

        $this->documentRepo->shouldReceive('fetchUnlinkedOcDocumentsForEntity')->with($entity)
            ->andReturn($docCollection);

        if (!empty($expected)) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage(var_export($expected, true));
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut->validate($entity, $command, false);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validateWithErrorsExternal')]
    public function testValidateWithErrorsExternal(mixed $isPsv, mixed $isRestricted, mixed $commandData, mixed $expected): void
    {
        $entity = m::mock();
        $entity->shouldReceive('isPsv')->andReturn($isPsv);
        $entity->shouldReceive('isRestricted')->andReturn($isRestricted);
        $entity->shouldReceive('isGoods')->andReturn(!$isPsv);

        $command = CreateOperatingCentre::create($commandData);

        $docCollection = new ArrayCollection();

        $this->documentRepo->shouldReceive('fetchUnlinkedOcDocumentsForEntity')->with($entity)
            ->andReturn($docCollection);

        try {
            $this->sut->validate($entity, $command, true);
            // If we are expecting errors, but the validate method didn't throw an exception
            if (!empty($expected)) {
                $this->fail('Validation Exception was not thrown');
            }
        } catch (ValidationException $ex) {
            // If we were not expecting any errors, but the exception was thrown
            if (empty($expected)) {
                $this->fail('Validation Exception was thrown');
            }

            $this->assertEquals($expected, $ex->getMessages());
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validateWithErrors')]
    public function testValidateUpdateWithErrors(mixed $isPsv, mixed $isRestricted, mixed $commandData, mixed $expected): void
    {
        $entity = m::mock();
        $entity->shouldReceive('isPsv')->andReturn($isPsv);
        $entity->shouldReceive('isRestricted')->andReturn($isRestricted);
        $entity->shouldReceive('isGoods')->andReturn(!$isPsv);

        $command = CreateOperatingCentre::create($commandData);

        $xoc = m::mock();

        $docCollection = new ArrayCollection();

        $xoc->shouldReceive('getOperatingCentre->getAdDocuments')->andReturn($docCollection);

        if (!empty($expected)) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage(var_export($expected, true));
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut->validate($entity, $command, false, $xoc);
    }

    public function testValidateTrafficAreaWithoutPostcode(): void
    {
        $commandData = [
            'address' => [
                'postcode' => null
            ]
        ];

        $entity = m::mock();

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals([], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeGbWithoutTa(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('N');
        $entity->shouldReceive('getTrafficArea')->andReturn(null);

        $command = CreateOperatingCentre::create($commandData);

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn(null);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals([], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaValidaetSameTrafficAreas(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ],
            'isTaOverridden' => 'N'
        ];

        $trafficArea = m::mock(TrafficArea::class);
        $trafficArea->shouldReceive('getId')->andReturn('TA');

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->shouldReceive('getTrafficArea')->andReturn($trafficArea);

        $command = CreateOperatingCentre::create($commandData);

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($trafficArea);

        $this->trafficAreaValidator->shouldReceive('validateForSameTrafficAreas')
            ->with($entity, 'TA')->andReturn(['CODE' => 'MESSAGE']);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals(['postcode' => [['CODE' => 'MESSAGE']]], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeGbWithTaWithoutMatchingPostcode(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];

        $ta = m::mock();

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn(null);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals([], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeGbWithTaWithMatchingPostcodeWithWrongTa(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ],
            'taIsOverridden' => 'N'
        ];

        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setName('Foo');

        $wrongTa = m::mock(TrafficArea::class)->makePartial();
        $wrongTa->setName('Bar');

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($wrongTa);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('N');
        $entity->shouldReceive('getTrafficArea')->andReturn($ta);

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $messages = [
            'postcode' => [
                [
                    'ERR_OC_PC_TA_GB' => '{"current":"Foo","oc":"Bar"}'
                ]
            ]
        ];
        $this->assertEquals($messages, $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeGbWithTaWithMatchingPostcodeWithMatchingTa(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];

        $ta = m::mock(TrafficArea::class)->makePartial();

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($ta);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('N');
        $entity->shouldReceive('getTrafficArea')->andReturn($ta);

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals([], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeNiWithoutMatchingPostcode(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn(null);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('Y');

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals([], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeNiWithMatchingPostcodeWithNiTa(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];

        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setId(TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($ta);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('Y');

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $this->assertEquals([], $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeNiWithMatchingPostcodeWithoutNiTa(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];

        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setId(TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE);

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($ta);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('Y');

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $messages = [
            'postcode' => [
                [
                    'ERR_OC_PC_TA_NI' => 'ERR_OC_PC_TA_NI'
                ]
            ]
        ];

        $this->assertEquals($messages, $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithPostcodeNiAndNewAppGb(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ]
        ];
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setId(TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($ta)
            ->once();

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(false);
        $entity->setNiFlag('N');

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $messages = [
            'postcode' => [
                [
                    'ERR_OC_TA_NI_APP' => 'ERR_OC_TA_NI_APP'
                ]
            ]
        ];

        $this->assertEquals($messages, $this->sut->getMessages());
    }

    public function testSaveDocuments(): void
    {
        $entity = m::mock();

        $operatingCentre = m::mock(OperatingCentre::class);

        /** @var \Dvsa\Olcs\Api\Entity\Doc\Document $document */
        $document = m::mock(\Dvsa\Olcs\Api\Entity\Doc\Document::class)->makePartial();

        $documents = [
            $document
        ];

        $documentRepo = m::mock(DocumentRepository::class);
        $documentRepo->shouldReceive('fetchUnlinkedOcDocumentsForEntity')
            ->with($entity)
            ->andReturn($documents)
            ->shouldReceive('save')
            ->with($document);

        $this->sut->saveDocuments($entity, $operatingCentre, $documentRepo);

        $this->assertSame($operatingCentre, $document->getOperatingCentre());
    }

    public function testCreateOperatingCentre(): void
    {
        $data = [
            'address' => [
                'addressLine1' => '123 foo street'
            ]
        ];
        $command = CreateOperatingCentre::create($data);

        $result = new Result();
        $result->addId('address', 123);

        $commandHandler = m::mock(CommandHandlerManager::class);
        $commandHandler->shouldReceive('handleCommand')
            ->with(m::type(SaveAddress::class), false)
            ->andReturn($result);

        $result = new Result();

        $address = m::mock(Address::class);

        $ocRepo = m::mock(\Dvsa\Olcs\Api\Domain\Repository\OperatingCentre::class);
        $ocRepo->shouldReceive('getReference')
            ->with(Address::class, 123)
            ->andReturn($address)
            ->shouldReceive('save')
            ->with(m::type(OperatingCentre::class));

        $oc = $this->sut->createOperatingCentre($command, $commandHandler, $result, $ocRepo);

        $expected = [
            'id' => [
                'address' => 123
            ],
            'messages' => []
        ];

        $this->assertSame($address, $oc->getAddress());
        $this->assertEquals($expected, $result->toArray());
    }

    public function testUpdateOperatingCentreLinkPsv(): void
    {
        $data = [
            'noOfVehiclesRequired' => 10,
            'noOfTrailersRequired' => 11,
            'permission' => 'Y',
        ];
        $command = CreateOperatingCentre::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->initCollections();
        $licence->shouldReceive('isPsv')->andReturn(true);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();

        $repo = m::mock();
        $repo->shouldReceive('save')->once()->with($loc);

        $this->sut->updateOperatingCentreLink($loc, $licence, $command, $repo);

        $this->assertEquals(10, $loc->getNoOfVehiclesRequired());
        $this->assertNull($loc->getNoOfTrailersRequired());
        $this->assertEquals('Y', $loc->getPermission());
    }

    public function testUpdateOperatingCentreLink(): void
    {
        $data = [
            'noOfVehiclesRequired' => 10,
            'noOfTrailersRequired' => 11,
            'permission' => 'Y',
            'adPlaced' => ApplicationOperatingCentre::AD_UPLOAD_NOW,
            'adPlacedIn' => 'Foo',
            'adPlacedDate' => '2015-01-01'
        ];
        $command = CreateOperatingCentre::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->initCollections();
        $licence->shouldReceive('isPsv')->andReturn(false);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();

        $repo = m::mock();
        $repo->shouldReceive('save')->once()->with($loc);

        $this->sut->updateOperatingCentreLink($loc, $licence, $command, $repo);

        $this->assertEquals(10, $loc->getNoOfVehiclesRequired());
        $this->assertEquals(11, $loc->getNoOfTrailersRequired());
        $this->assertEquals('Y', $loc->getPermission());

        $this->assertEquals(ApplicationOperatingCentre::AD_UPLOAD_NOW, $loc->getAdPlaced());
        $this->assertEquals('Foo', $loc->getAdPlacedIn());
        $this->assertEquals('2015-01-01', $loc->getAdPlacedDate()->format('Y-m-d'));
    }

    public static function validateWithErrors(): array
    {
        return [
            [
                true,
                false,
                [
                    'noOfVehiclesRequired' => 0
                ],
                [
                    'noOfVehiclesRequired' => [
                        [
                            'ERR_OC_VR_1B' => 'ERR_OC_VR_1B'
                        ]
                    ]
                ]
            ],
            [
                true,
                true,
                [
                    'noOfVehiclesRequired' => 3
                ],
                [
                    'noOfVehiclesRequired' => [
                        [
                            'ERR_OR_R_TOO_MANY' => 'ERR_OR_R_TOO_MANY'
                        ]
                    ]
                ]
            ],
            [
                false,
                false,
                [
                    'noOfVehiclesRequired' => 0,
                    'noOfTrailersRequired' => 0,
                    'adPlaced' => ApplicationOperatingCentre::AD_POST
                ],
                [
                    'noOfVehiclesRequired' => [
                        [
                            'ERR_OC_VR_1A' => 'ERR_OC_VR_1A'
                        ]
                    ],
                    'noOfTrailersRequired' => [
                        [
                            'ERR_OC_VR_1A' => 'ERR_OC_VR_1A'
                        ]
                    ]
                ]
            ],
            [
                false,
                false,
                [
                    'noOfVehiclesRequired' => 0,
                    'noOfTrailersRequired' => 1,
                    'adPlaced' => ApplicationOperatingCentre::AD_UPLOAD_NOW,
                    'adPlacedIn' => '',
                    'adPlacedDate' => ''
                ],
                [
                    'adPlacedIn' => [
                        [
                            'ERR_OC_AD_IN_1' => 'ERR_OC_AD_IN_1'
                        ]
                    ],
                    'adPlacedDate' => [
                        [
                            'ERR_OC_AD_DT_1' => 'ERR_OC_AD_DT_1'
                        ]
                    ]
                ]
            ],
            [
                false,
                false,
                [
                    'noOfVehiclesRequired' => 0,
                    'noOfTrailersRequired' => 1,
                    'adPlaced' => ApplicationOperatingCentre::AD_UPLOAD_NOW,
                    'adPlacedIn' => 'sasdasd',
                    'adPlacedDate' => 'asdsad'
                ],
                [
                ]
            ]
        ];
    }

    public static function validateWithErrorsExternal(): array
    {
        return [
            [
                true,
                false,
                [
                    'noOfVehiclesRequired' => 0
                ],
                [
                    'noOfVehiclesRequired' => [
                        [
                            'ERR_OC_VR_1B' => 'ERR_OC_VR_1B'
                        ]
                    ],
                    'permission' => [
                        [
                            'ERR_OC_PERMISSION' => 'ERR_OC_PERMISSION'
                        ]
                    ]
                ]
            ],
            [
                true,
                true,
                [
                    'noOfVehiclesRequired' => 3
                ],
                [
                    'noOfVehiclesRequired' => [
                        [
                            'ERR_OR_R_TOO_MANY' => 'ERR_OR_R_TOO_MANY'
                        ]
                    ],
                    'permission' => [
                        [
                            'ERR_OC_PERMISSION' => 'ERR_OC_PERMISSION'
                        ]
                    ]
                ]
            ],
            [
                false,
                false,
                [
                    'noOfVehiclesRequired' => 0,
                    'noOfTrailersRequired' => 0,
                    'adPlaced' => ApplicationOperatingCentre::AD_POST
                ],
                [
                    'noOfVehiclesRequired' => [
                        [
                            'ERR_OC_VR_1A' => 'ERR_OC_VR_1A'
                        ]
                    ],
                    'noOfTrailersRequired' => [
                        [
                            'ERR_OC_VR_1A' => 'ERR_OC_VR_1A'
                        ]
                    ],
                    'permission' => [
                        [
                            'ERR_OC_PERMISSION' => 'ERR_OC_PERMISSION'
                        ]
                    ]
                ]
            ],
            [
                false,
                false,
                [
                    'noOfVehiclesRequired' => 0,
                    'noOfTrailersRequired' => 1,
                    'adPlaced' => ApplicationOperatingCentre::AD_UPLOAD_NOW,
                    'adPlacedIn' => '',
                    'adPlacedDate' => ''
                ],
                [
                    'adPlacedIn' => [
                        [
                            'ERR_OC_AD_IN_1' => 'ERR_OC_AD_IN_1'
                        ]
                    ],
                    'adPlacedDate' => [
                        [
                            'ERR_OC_AD_DT_1' => 'ERR_OC_AD_DT_1'
                        ]
                    ],
                    'file' => [
                        [
                            'ERR_OC_AD_FI_1' => 'ERR_OC_AD_FI_1'
                        ]
                    ],
                    'permission' => [
                        [
                            'ERR_OC_PERMISSION' => 'ERR_OC_PERMISSION'
                        ]
                    ]
                ]
            ],
            [
                false,
                false,
                [
                    'noOfVehiclesRequired' => 0,
                    'noOfTrailersRequired' => 1,
                    'adPlaced' => ApplicationOperatingCentre::AD_UPLOAD_NOW,
                    'adPlacedIn' => 'sasdasd',
                    'adPlacedDate' => 'asdsad'
                ],
                [
                    'file' => [
                        [
                            'ERR_OC_AD_FI_1' => 'ERR_OC_AD_FI_1'
                        ]
                    ],
                    'permission' => [
                        [
                            'ERR_OC_PERMISSION' => 'ERR_OC_PERMISSION'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function testValidateConfirmations(): void
    {
        $data = [
            'noOfVehiclesRequired' => 10,
            'noOfTrailersRequired' => 11,
            'adPlaced' => ApplicationOperatingCentre::AD_UPLOAD_NOW,
            'adPlacedIn' => 'Foo',
            'adPlacedDate' => '2015-01-01',
            'permission' => ''
        ];
        $entity = m::mock()
            ->shouldReceive('isPsv')
            ->andReturn(false)
            ->shouldReceive('isGoods')
            ->andReturn(false)
            ->getMock();

        $this->expectException(ValidationException::class);
        $command = CreateOperatingCentre::create($data);
        $this->sut->validate($entity, $command, true);

        $errors = [
            'permission' => [
                [
                    'ERR_OC_PERMISSION' => 'ERR_OC_PERMISSION'
                ]
            ]
        ];

        $this->assertEquals($errors, $this->sut->getMessages());
    }

    public function testValidateTrafficAreaWithAddressServiceNotWorking(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'SW1A 1AA'
            ]
        ];
        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('SW1A 1AA')
            ->andThrow(new \Exception());

        $entity = m::mock();
        $command = CreateOperatingCentre::create($commandData);

        $this->assertNull($this->sut->validateTrafficArea($entity, $command));
    }

    public function testValidateOverriddenTA(): void
    {
        $commandData = [
            'address' => [
                'postcode' => 'AA11AAA'
            ],
            'taIsOverridden' => 'Y'
        ];

        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setName('Foo');

        $wrongTa = m::mock(TrafficArea::class)->makePartial();
        $wrongTa->setName('Bar');

        $this->addressService->expects('fetchTrafficAreaByPostcodeOrUprn')
            ->with('AA11AAA')
            ->andReturn($wrongTa);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setIsVariation(true);
        $entity->setNiFlag('N');
        $entity->shouldReceive('getTrafficArea')->andReturn($ta);

        $command = CreateOperatingCentre::create($commandData);

        $this->sut->validateTrafficArea($entity, $command);

        $messages = [];

        $this->assertEquals($messages, $this->sut->getMessages());
    }
}
