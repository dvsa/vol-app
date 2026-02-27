<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\SafetyReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Safety review service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SafetyReviewServiceTest extends MockeryTestCase
{
    /** @var SafetyReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class)
            ->shouldReceive('translate')
            ->andReturnUsing(
                fn($arg) => $arg . '_translated'
            )
            ->getMock();

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new SafetyReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $continuationDetail = new ContinuationDetail();

        $workshops = new ArrayCollection();

        $workshop1 = m::mock()
            ->shouldReceive('getContactDetails')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getAddress')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getAddressLine1')
                            ->andReturn('Cake')
                            ->once()
                            ->shouldReceive('getTown')
                            ->andReturn('Baz')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getFao')
                    ->andReturn('Name 2')
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('getIsExternal')
            ->andReturn('N')
            ->once()
            ->getMock();

        $workshop2 = m::mock()
            ->shouldReceive('getContactDetails')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getAddress')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getAddressLine1')
                            ->andReturn('Foo')
                            ->once()
                            ->shouldReceive('getTown')
                            ->andReturn('Bar')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getFao')
                    ->andReturn('Name 1')
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('getIsExternal')
            ->andReturn('Y')
            ->once()
            ->getMock();

        $workshops->add($workshop1);
        $workshops->add($workshop2);

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getWorkshops')
            ->andReturn($workshops)
            ->once()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                ['value' => 'continuations.safety-section.table.inspector', 'header' => true],
                ['value' => 'continuations.safety-section.table.address', 'header' => true],
            ],
            [
                ['value' => 'Name 1 (continuations.safety-section.table.external-contractor_translated)'],
                ['value' => 'Foo, Bar'],
            ],
            [
                ['value' => 'Name 2 (continuations.safety-section.table.owner-or-employee_translated)'],
                ['value' => 'Cake, Baz'],
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }

    public function testGetSummaryFromData(): void
    {
        $continuationDetail = new ContinuationDetail();

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getSafetyInsVehicles')
            ->andReturn(2)
            ->times(3)
            ->shouldReceive('canHaveTrailer')
            ->andReturn(true)
            ->twice()
            ->shouldReceive('getSafetyInsTrailers')
            ->andReturn(3)
            ->times(3)
            ->shouldReceive('getSafetyInsVaries')
            ->andReturn('Y')
            ->times(2)
            ->shouldReceive('getTachographIns')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(Licence::TACH_EXT)
                ->once()
                ->getMock()
            )
            ->twice()
            ->shouldReceive('getTachographInsName')
            ->andReturn('foo')
            ->twice()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                [
                    'value' => 'continuations.safety-section.table.max-time-vehicles_translated',
                    'header' => true
                ],
                [
                    'value' => '2 ' . 'continuations.safety-section.table.weeks_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.max-time-trailers_translated',
                    'header' => true
                ],
                [
                    'value' => '3 ' . 'continuations.safety-section.table.weeks_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.varies_translated',
                    'header' => true
                ],
                [
                    'value' => 'Yes_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.tachographs_translated',
                    'header' => true
                ],
                [
                    'value' => 'continuations.safety-section.table.' . Licence::TACH_EXT . '_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.tachographInsName_translated',
                    'header' => true
                ],
                [
                    'value' => 'foo',
                ]
            ],
        ];

        $this->assertEquals($expected, $this->sut->getSummaryFromData($continuationDetail));
    }

    public function testGetSummaryFromDataNoTrailers(): void
    {
        $continuationDetail = new ContinuationDetail();

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getSafetyInsVehicles')
            ->withNoArgs()
            ->andReturn(2)
            ->shouldReceive('canHaveTrailer')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('getSafetyInsTrailers')
            ->withNoArgs()
            ->andReturn(3)
            ->shouldReceive('getSafetyInsVaries')
            ->withNoArgs()
            ->andReturn('Y')
            ->shouldReceive('getTachographIns')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(Licence::TACH_EXT)
                ->once()
                ->getMock()
            )
            ->shouldReceive('getTachographInsName')
            ->withNoArgs()
            ->andReturn('foo')
            ->twice()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                [
                    'value' => 'continuations.safety-section.table.max-time-vehicles_translated',
                    'header' => true
                ],
                [
                    'value' => '2 ' . 'continuations.safety-section.table.weeks_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.varies.no-trailers_translated',
                    'header' => true
                ],
                [
                    'value' => 'Yes_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.tachographs_translated',
                    'header' => true
                ],
                [
                    'value' => 'continuations.safety-section.table.' . Licence::TACH_EXT . '_translated',
                ]
            ],
            [
                [
                    'value' => 'continuations.safety-section.table.tachographInsName_translated',
                    'header' => true
                ],
                [
                    'value' => 'foo',
                ]
            ],
        ];

        $this->assertEquals($expected, $this->sut->getSummaryFromData($continuationDetail));
    }

    public function testGetSummaryHeader(): void
    {
        $this->assertEquals(
            'continuations.safety-details.label',
            $this->sut->getSummaryHeader()
        );
    }
}
