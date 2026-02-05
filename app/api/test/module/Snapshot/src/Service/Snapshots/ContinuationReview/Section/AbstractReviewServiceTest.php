<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section\Stub\AbstractReviewServiceStub;
use Laminas\I18n\Translator\TranslatorInterface;

class AbstractReviewServiceTest extends MockeryTestCase
{
    private $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new AbstractReviewServiceStub($abstractReviewServiceServices);
    }

    public function testTranslate(): void
    {
        $this->mockTranslator
            ->shouldReceive('translate')
            ->with('foo')
            ->andReturn('foo_translated')
            ->once()
            ->getMock();

        $this->assertEquals('foo_translated', $this->sut->translate('foo'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatDateDataProvider')]
    public function testFormatDate(mixed $expected, mixed $date): void
    {
        $this->assertEquals($expected, $this->sut->formatDate($date));
    }

    public static function formatDateDataProvider(): array
    {
        return [
            ['15 Aug 2005', '2005-08-15T15:52:01+00:00'],
            ['01 Aug 2017', '2017-08-01T15:52:00+05:00'],
        ];
    }
}
