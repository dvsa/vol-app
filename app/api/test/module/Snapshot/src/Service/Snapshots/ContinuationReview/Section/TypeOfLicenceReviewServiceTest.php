<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\TypeOfLicenceReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Type of licence review service test
 */
class TypeOfLicenceReviewServiceTest extends MockeryTestCase
{
    /** @var  TypeOfLicence review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new TypeOfLicenceReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromDataGb(): void
    {
        $gbContinuationDetail = new ContinuationDetail();

        $mockLicenceGb = m::mock(Licence::class)
            ->shouldReceive('getNiFlag')
            ->andReturn('N')
            ->once()
            ->shouldReceive('getOperatorType')
            ->andReturn('Goods')
            ->once()
            ->shouldReceive('getOperatorLocation')
            ->andReturn('Great Britain')
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getDescription')
                    ->andReturn('foo')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $gbContinuationDetail->setLicence($mockLicenceGb);

        $expected = [
            [
                ['value' => 'continuation-review-operator-location'],
                ['value' => 'Great Britain', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-operator-type'],
                ['value' => 'Goods', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-licence-type'],
                ['value' => 'foo', 'header' => true]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($gbContinuationDetail));
    }

    public function testGetConfigFromDataNi(): void
    {
        $niContinuationDetail = new ContinuationDetail();

        $mockLicenceNi = m::mock(Licence::class)
            ->shouldReceive('getNiFlag')
            ->andReturn('Y')
            ->once()
            ->shouldReceive('getOperatorLocation')
            ->andReturn('Northern Ireland')
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getDescription')
                    ->andReturn('foo')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $niContinuationDetail->setLicence($mockLicenceNi);

        $expected = [
            [
                ['value' => 'continuation-review-operator-location'],
                ['value' => 'Northern Ireland', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-licence-type'],
                ['value' => 'foo', 'header' => true]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($niContinuationDetail));
    }
}
