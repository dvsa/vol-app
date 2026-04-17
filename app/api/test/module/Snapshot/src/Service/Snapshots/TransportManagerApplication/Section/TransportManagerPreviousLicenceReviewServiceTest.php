<?php

declare(strict_types=1);

/**
 * Transport Manager Previous Licence Review Service Test
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
 * Transport Manager Previous Licence Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerPreviousLicenceReviewServiceTest extends MockeryTestCase
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

        $this->sut = new TransportManagerApplication\Section\TransportManagerPreviousLicenceReviewService(
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
        $tm1->setOtherLicences(new ArrayCollection());

        /** @var Entity\Tm\TransportManagerApplication $tma1 */
        $tma1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tma1->setTransportManager($tm1);

        /** @var Entity\OtherLicence\OtherLicence $otherLicence1 */
        $otherLicence1 = m::mock(Entity\OtherLicence\OtherLicence::class)->makePartial();
        $otherLicence1->setLicNo('AB12345678');
        $otherLicence1->setHolderName('Some holder');

        /** @var Entity\OtherLicence\OtherLicence $otherLicence2 */
        $otherLicence2 = m::mock(Entity\OtherLicence\OtherLicence::class)->makePartial();
        $otherLicence2->setLicNo('BA12345678');
        $otherLicence2->setHolderName('Some other holder');

        $otherLicences = new ArrayCollection();
        $otherLicences->add($otherLicence1);
        $otherLicences->add($otherLicence2);

        /** @var Entity\Tm\TransportManager $tm2 */
        $tm2 = m::mock(Entity\Tm\TransportManager::class)->makePartial();
        $tm2->setOtherLicences($otherLicences);

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
                                    'freetext' => 'tm-review-previous-licence-none-translated'
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
                                    'header' => 'AB12345678',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-previous-licence-licNo',
                                                'value' => 'AB12345678'
                                            ],
                                            [
                                                'label' => 'tm-review-previous-licence-holder',
                                                'value' => 'Some holder'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'BA12345678',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'tm-review-previous-licence-licNo',
                                                'value' => 'BA12345678'
                                            ],
                                            [
                                                'label' => 'tm-review-previous-licence-holder',
                                                'value' => 'Some other holder'
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
