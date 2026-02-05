<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\TransportManagersReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * TransportManagers review service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagersReviewServiceTest extends MockeryTestCase
{
    /** @var TransportManagersReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new TransportManagersReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $continuationDetail = new ContinuationDetail();

        $tmLicences = new ArrayCollection();

        $tmLic1 = m::mock()
            ->shouldReceive('getTransportManager')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getHomeCd')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getPerson')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('getForename')
                                    ->andReturn('foo')
                                    ->once()
                                    ->shouldReceive('getFamilyName')
                                    ->andReturn('bar')
                                    ->once()
                                    ->shouldReceive('getTitle')
                                    ->andReturn(null)
                                    ->once()
                                    ->shouldReceive('getBirthDate')
                                    ->with(true)
                                    ->andReturn(new \DateTime('1970-01-01'))
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $tmLic2 = m::mock()
            ->shouldReceive('getTransportManager')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getHomeCd')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getPerson')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('getForename')
                                    ->andReturn('cake')
                                    ->once()
                                    ->shouldReceive('getFamilyName')
                                    ->andReturn('baz')
                                    ->once()
                                    ->shouldReceive('getTitle')
                                    ->andReturn(
                                        m::mock()
                                            ->shouldReceive('getDescription')
                                            ->andReturn('Mr')
                                            ->once()
                                            ->getMock()
                                    )
                                    ->twice()
                                    ->shouldReceive('getBirthDate')
                                    ->with(true)
                                    ->andReturn(null)
                                    ->once()
                                    ->getMock()
                            )
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $tmLicences->add($tmLic1);
        $tmLicences->add($tmLic2);

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getTmLicences')
            ->andReturn($tmLicences)
            ->once()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                ['value' => 'continuations.tm-section.table.name', 'header' => true],
                ['value' => 'continuations.tm-section.table.dob', 'header' => true],
            ],
            [
                ['value' => 'Mr cake baz'],
                ['value' => ''],
            ],
            [
                ['value' => 'foo bar'],
                ['value' => '01/01/1970'],
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }
}
