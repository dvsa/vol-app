<?php

declare(strict_types=1);

/**
 * Transport Manager Other Employment Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Transport Manager Other Employment Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerOtherEmploymentReviewServiceTest extends MockeryTestCase
{
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

        $this->sut = new TransportManagerApplication\Section\TransportManagerOtherEmploymentReviewService(
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
        $tm1->setEmployments(new ArrayCollection());

        /** @var Entity\Tm\TransportManagerApplication $tma1 */
        $tma1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tma1->setTransportManager($tm1);

        /** @var Entity\ContactDetails\Address $address */
        $address = m::mock(Entity\ContactDetails\Address::class)->makePartial();
        $address->setAddressLine1('Foo street');
        $address->setTown('Footown');

        /** @var Entity\ContactDetails\ContactDetails $contactDetails */
        $contactDetails = m::mock(Entity\ContactDetails\ContactDetails::class)->makePartial();
        $contactDetails->setAddress($address);

        /** @var Entity\Tm\TmEmployment $employment1 */
        $employment1 = m::mock(Entity\Tm\TmEmployment::class)->makePartial();
        $employment1->setEmployerName('Tesco');
        $employment1->setPosition('Boss');
        $employment1->setHoursPerWeek('All night long');
        $employment1->setContactDetails($contactDetails);

        /** @var Entity\Tm\TmEmployment $employment2 */
        $employment2 = m::mock(Entity\Tm\TmEmployment::class)->makePartial();
        $employment2->setEmployerName('Asda');
        $employment2->setPosition('Bossing around');
        $employment2->setHoursPerWeek('24/7');
        $employment2->setContactDetails($contactDetails);

        $employments = new ArrayCollection();
        $employments->add($employment1);
        $employments->add($employment2);

        /** @var Entity\Tm\TransportManager $tm2 */
        $tm2 = m::mock(Entity\Tm\TransportManager::class)->makePartial();
        $tm2->setEmployments($employments);

        /** @var Entity\Tm\TransportManagerApplication $tma2 */
        $tma2 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tma2->setTransportManager($tm2);

        return [
            [
                $tma1,
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'freetext' => 'tm-review-other-employment-none-translated'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                $tma2,
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'header' => 'Tesco',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-other-employment-address',
                                                'value' => 'Foo street, Footown'
                                            ],
                                            [
                                                'label' => 'tm-review-other-employment-position',
                                                'value' => 'Boss'
                                            ],
                                            [
                                                'label' => 'tm-review-other-employment-hours-per-week',
                                                'value' => 'All night long'
                                            ],
                                            [
                                                'label' => 'tm-review-other-employment-confirmation',
                                                'value' => 'transportManager.data.availability.understoodAvailabilityAgreementConfirmation-translated'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'Asda',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-other-employment-address',
                                                'value' => 'Foo street, Footown'
                                            ],
                                            [
                                                'label' => 'tm-review-other-employment-position',
                                                'value' => 'Bossing around'
                                            ],
                                            [
                                                'label' => 'tm-review-other-employment-hours-per-week',
                                                'value' => '24/7'
                                            ],
                                            [
                                                'label' => 'tm-review-other-employment-confirmation',
                                                'value' => 'transportManager.data.availability.understoodAvailabilityAgreementConfirmation-translated'
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
