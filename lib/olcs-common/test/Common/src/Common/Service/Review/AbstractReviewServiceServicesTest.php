<?php

namespace CommonTest\Service\Review;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Review\AbstractReviewServiceServices;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Abstract Review Service Services Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.ku>
 */
class AbstractReviewServiceServicesTest extends MockeryTestCase
{
    public function testGetTranslationHelper(): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);

        $sut = new AbstractReviewServiceServices($translationHelper);

        $this->assertSame(
            $translationHelper,
            $sut->getTranslationHelper()
        );
    }
}
