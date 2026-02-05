<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices
 */
class AbstractReviewServiceServicesTest extends MockeryTestCase
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
