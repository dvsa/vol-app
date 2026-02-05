<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\Translator\TranslatorInterface;

#[\PHPUnit\Framework\Attributes\CoversClass('::class')]
class TransportManagerResponsibilityReviewServiceTest extends MockeryTestCase
{
    /** @var  TransportManagerApplication\Section\TransportManagerResponsibilityReviewService */
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(
            TransportManagerApplication\Section\AbstractReviewServiceServices::class
        );
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new TransportManagerApplication\Section\TransportManagerResponsibilityReviewService(
            $abstractReviewServiceServices
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testGetConfig(mixed $tma, mixed $expected): void
    {
        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfig($tma));
    }

    public static function provider(): array
    {
        /** @var Entity\Tm\TransportManager $tm1 */
        $tm1 = m::mock(Entity\Tm\TransportManager::class)->makePartial();
        $tm1->setDocuments(new ArrayCollection());

        /** @var Entity\System\RefData $internalType */
        $internalType = m::mock(Entity\System\RefData::class)->makePartial();
        $internalType->setDescription('Internal');

        /** @var Entity\ContactDetails\Address $address1 */
        $address1 = m::mock(Entity\ContactDetails\Address::class)->makePartial();
        $address1->setAddressLine1('Foo');

        /** @var Entity\ContactDetails\Address $address2 */
        $address2 = m::mock(Entity\ContactDetails\Address::class)->makePartial();
        $address2->setAddressLine1('Bar');

        /** @var Entity\Tm\TransportManagerApplication $tma1 */
        $tma1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tma1->setIsOwner('Y');
        $tma1->setHoursMon(2);
        $tma1->setHoursTue(3);
        $tma1->setHoursWed(4);
        $tma1->setHoursThu(5);
        $tma1->setHoursFri(6);
        $tma1->setHoursSat(7);
        $tma1->setHoursSun(8);
        $tma1->setAdditionalInformation('Foo bar cake');
        $tma1->setOtherLicences(new ArrayCollection());
        $tma1->setTmType($internalType);
        $tma1->setTransportManager($tm1);

        /** @var Entity\System\Category $cat1 */
        $cat1 = m::mock(Entity\System\Category::class)->makePartial();
        $cat1->setId(Entity\System\Category::CATEGORY_TRANSPORT_MANAGER);

        /** @var Entity\System\SubCategory $subCat1 */
        $subCat1 = m::mock(Entity\System\SubCategory::class)->makePartial();
        $subCat1->setId(Entity\System\Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL);

        /** @var Entity\Doc\Document $document1 */
        $document1 = m::mock(Entity\Doc\Document::class)->makePartial();
        $document1->setDescription('unit_File1Desc');
        $document1->setCategory($cat1);
        $document1->setSubCategory($subCat1);

        /** @var Entity\Doc\Document $document2 */
        $document2 = m::mock(Entity\Doc\Document::class)->makePartial();
        $document2->setDescription('unit_File2Desc');
        $document2->setCategory($cat1);
        $document2->setSubCategory($subCat1);

        /** @var  ArrayCollection|m\MockInterface $documents */
        $documents = new ArrayCollection();
        $documents->add($document1);
        $documents->add($document2);

        /** @var Entity\Tm\TransportManager $tm2 */
        $tm2 = m::mock(Entity\Tm\TransportManager::class)->makePartial();
        $tm2->setDocuments($documents);

        $tma2 = clone $tma1;
        $tma2->setTransportManager($tm2);

        /** @var Entity\System\RefData $role */
        $role = m::mock(Entity\System\RefData::class)->makePartial();
        $role->setDescription('Transport manager');

        /** @var Entity\OtherLicence\OtherLicence $otherLicence1 */
        $otherLicence1 = m::mock(Entity\OtherLicence\OtherLicence::class)->makePartial();
        $otherLicence1->setLicNo('AB12345678');
        $otherLicence1->setOperatingCentres(10);
        $otherLicence1->setTotalAuthVehicles(20);
        $otherLicence1->setHoursPerWeek(30);
        $otherLicence1->setRole($role);

        /** @var Entity\OtherLicence\OtherLicence $otherLicence2 */
        $otherLicence2 = m::mock(Entity\OtherLicence\OtherLicence::class)->makePartial();
        $otherLicence2->setLicNo('BA98765421');
        $otherLicence2->setOperatingCentres(20);
        $otherLicence2->setTotalAuthVehicles(10);
        $otherLicence2->setHoursPerWeek(15);
        $otherLicence2->setRole($role);

        $otherLicences = new ArrayCollection();
        $otherLicences->add($otherLicence1);
        $otherLicences->add($otherLicence2);

        $tma3 = clone $tma2;
        $tma3->setOtherLicences($otherLicences);

        return [
            [
                'tma' => $tma1,
                'expected' => [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-tmType',
                                                'value' => 'Internal'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-isOwner',
                                                'value' => 'Yes'
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'header' => 'tm-review-responsibility-hours-per-week-header',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-mon',
                                                'value' => '2 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-tue',
                                                'value' => '3 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-wed',
                                                'value' => '4 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-thu',
                                                'value' => '5 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-fri',
                                                'value' => '6 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-sat',
                                                'value' => '7 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-sun',
                                                'value' => '8 hours-translated'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'tm-review-responsibility-other-licences',
                            'mainItems' => [
                                [
                                    'freetext' => 'tm-review-responsibility-other-licences-none-added-translated'
                                ]
                            ]
                        ],
                        [
                            'title' => 'tm-review-responsibility-additional-info-header',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-additional-info',
                                                'value' => 'Foo bar cake'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-additional-info-files',
                                                'noEscape' => true,
                                                'value' => 'tm-review-responsibility-no-files-translated'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'tma' => $tma2,
                'expected' => [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-tmType',
                                                'value' => 'Internal'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-isOwner',
                                                'value' => 'Yes'
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'header' => 'tm-review-responsibility-hours-per-week-header',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-mon',
                                                'value' => '2 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-tue',
                                                'value' => '3 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-wed',
                                                'value' => '4 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-thu',
                                                'value' => '5 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-fri',
                                                'value' => '6 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-sat',
                                                'value' => '7 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-sun',
                                                'value' => '8 hours-translated'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'tm-review-responsibility-other-licences',
                            'mainItems' => [
                                [
                                    'freetext' => 'tm-review-responsibility-other-licences-none-added-translated'
                                ]
                            ]
                        ],
                        [
                            'title' => 'tm-review-responsibility-additional-info-header',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-additional-info',
                                                'value' => 'Foo bar cake'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-additional-info-files',
                                                'noEscape' => true,
                                                'value' => 'unit_File1Desc<br>unit_File2Desc',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'tma' => $tma3,
                'expected' => [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-tmType',
                                                'value' => 'Internal'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-isOwner',
                                                'value' => 'Yes'
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'header' => 'tm-review-responsibility-hours-per-week-header',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-mon',
                                                'value' => '2 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-tue',
                                                'value' => '3 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-wed',
                                                'value' => '4 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-thu',
                                                'value' => '5 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-fri',
                                                'value' => '6 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-sat',
                                                'value' => '7 hours-translated'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-sun',
                                                'value' => '8 hours-translated'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'tm-review-responsibility-other-licences',
                            'mainItems' => [
                                [
                                    'header' => 'AB12345678',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-role',
                                                'value' => 'Transport manager'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-operating-centres',
                                                'value' => 10
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-vehicles',
                                                'value' => 20
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-hours-per-week',
                                                'value' => 30
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'BA98765421',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-role',
                                                'value' => 'Transport manager'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-operating-centres',
                                                'value' => 20
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-vehicles',
                                                'value' => 10
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-other-licences-hours-per-week',
                                                'value' => 15
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'tm-review-responsibility-additional-info-header',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-responsibility-additional-info',
                                                'value' => 'Foo bar cake'
                                            ],
                                            [
                                                'label' => 'tm-review-responsibility-additional-info-files',
                                                'noEscape' => true,
                                                'value' => 'unit_File1Desc<br>unit_File2Desc',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
