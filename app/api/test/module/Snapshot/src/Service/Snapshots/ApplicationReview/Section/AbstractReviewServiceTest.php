<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section\Stub\AbstractReviewServiceStub;
use Laminas\I18n\Translator\TranslatorInterface;

class AbstractReviewServiceTest extends MockeryTestCase
{
    private $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new AbstractReviewServiceStub($abstractReviewServiceServices);
    }

    public function testGetHeaderTranslationKey(): void
    {
        $this->assertEquals(
            'review-section-name',
            $this->sut->getHeaderTranslationKey([], 'section-name')
        );
    }
}
