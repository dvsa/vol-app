<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\TransportManagerMainReviewService;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Transport Manager Main Review Service Test
 */
class TransportManagerMainReviewServiceTest extends MockeryTestCase
{
    /** @var  TransportManagerMainReviewService */
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new TransportManagerMainReviewService($abstractReviewServiceServices);
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
        /** @var RefData $title */
        $title = m::mock(RefData::class)->makePartial();
        $title->setDescription('Mr');

        /** @var Person $person */
        $person = m::mock(Person::class)->makePartial();
        $person->setTitle($title);
        $person->setForename('Foo');
        $person->setFamilyName('Bar');
        $person->setBirthDate('1989-08-23');
        $person->setBirthPlace('Footown');

        /** @var Address $address1 */
        $address1 = m::mock(Address::class)->makePartial();
        $address1->setAddressLine1('123 work street');

        /** @var Address $address2 */
        $address2 = m::mock(Address::class)->makePartial();
        $address2->setAddressLine1('123 home street');

        /** @var ContactDetails $contactDetails1 */
        $contactDetails1 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails1->setAddress($address1);

        /** @var ContactDetails $contactDetails2 */
        $contactDetails2 = m::mock(ContactDetails::class)->makePartial();
        $contactDetails2->setAddress($address2);
        $contactDetails2->setPerson($person);
        $contactDetails2->setEmailAddress('foo@bar.com');

        /** @var Category $cat1 */
        $cat1 = m::mock(Category::class)->makePartial();
        $cat1->setId(Category::CATEGORY_TRANSPORT_MANAGER);

        /** @var SubCategory $subCat1 */
        $subCat1 = m::mock(SubCategory::class)->makePartial();
        $subCat1->setId(Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION);

        /** @var Document $document1 */
        $document1 = m::mock(Document::class)->makePartial();
        $document1->setDescription('unit_File1Desc')
            ->setCategory($cat1)
            ->setSubCategory($subCat1);

        /** @var Document $document2 */
        $document2 = m::mock(Document::class)->makePartial();
        $document2->setDescription('unit_File2Desc')
            ->setCategory($cat1)
            ->setSubCategory($subCat1);

        /** @var ArrayCollection|m\MockInterface $documents */
        $documents = new ArrayCollection();
        $documents->add($document1);
        $documents->add($document2);

        /** @var TransportManager $tm1 */
        $tm1 = m::mock(TransportManager::class)->makePartial();
        $tm1->setWorkCd($contactDetails1);
        $tm1->setHomeCd($contactDetails2);
        $tm1->setDocuments($documents);

        /** @var TransportManagerApplication $tma1 */
        $tma1 = m::mock(TransportManagerApplication::class)->makePartial();
        $tma1->setTransportManager($tm1);
        $tma1->setHasUndertakenTraining('Y');

        /** @var TransportManager $tm2 */
        $tm2 = m::mock(TransportManager::class)->makePartial();
        $tm2->setWorkCd($contactDetails1);
        $tm2->setHomeCd($contactDetails2);
        $tm2->setDocuments(new ArrayCollection());

        /** @var TransportManagerApplication $tma2 */
        $tma2 = m::mock(TransportManagerApplication::class)->makePartial();
        $tma2->setTransportManager($tm2);
        $tma2->setHasUndertakenTraining('N');

        return [
            [
                $tma1,
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'tm-review-main-name',
                                'value' => 'Mr Foo Bar'
                            ],
                            [
                                'label' => 'tm-review-main-birthDate',
                                'value' => '23 Aug 1989'
                            ],
                            [
                                'label' => 'tm-review-main-birthPlace',
                                'value' => 'Footown'
                            ],
                            [
                                'label' => 'tm-review-main-email',
                                'value' => 'foo@bar.com'
                            ],
                            [
                                'label' => 'tm-review-main-certificate',
                                'noEscape' => true,
                                'value' => 'unit_File1Desc<br>unit_File2Desc',
                            ],
                            [
                                'label' => 'tm-review-responsibility-training-undertaken',
                                'value' => 'Yes',
                            ],
                            [
                                'label' => 'tm-review-main-home-address',
                                'value' => '123 home street'
                            ],
                            [
                                'label' => 'tm-review-main-work-address',
                                'value' => '123 work street'
                            ]
                        ]
                    ]
                ]
            ],
            [
                $tma2,
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'tm-review-main-name',
                                'value' => 'Mr Foo Bar'
                            ],
                            [
                                'label' => 'tm-review-main-birthDate',
                                'value' => '23 Aug 1989'
                            ],
                            [
                                'label' => 'tm-review-main-birthPlace',
                                'value' => 'Footown'
                            ],
                            [
                                'label' => 'tm-review-main-email',
                                'value' => 'foo@bar.com'
                            ],
                            [
                                'label' => 'tm-review-main-certificate',
                                'noEscape' => true,
                                'value' => 'tm-review-main-no-files-translated'
                            ],
                            [
                                'label' => 'tm-review-responsibility-training-undertaken',
                                'value' => 'No',
                            ],
                            [
                                'label' => 'tm-review-main-home-address',
                                'value' => '123 home street'
                            ],
                            [
                                'label' => 'tm-review-main-work-address',
                                'value' => '123 work street'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
