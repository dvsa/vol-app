<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices::class)]
final class AbstractReviewServiceServicesTest extends MockeryTestCase
{
    public function testGetTranslator(): void
    {
        $translator = m::mock(TranslatorInterface::class);

        $sut = new AbstractReviewServiceServices($translator);

        $this->assertSame(
            $translator,
            $sut->getTranslator()
        );
    }
}
