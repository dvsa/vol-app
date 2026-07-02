<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\DataTransformer\DataTransformerProvider;
use Common\Service\Qa\DataTransformer\DataTransformerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DataTransformerProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DataTransformerProviderTest extends MockeryTestCase
{
    public function testGetTransformer(): void
    {
        $slug = 'no-of-permits';
        $dataTransformer = m::mock(DataTransformerInterface::class);

        $sut = new DataTransformerProvider();
        $sut->registerTransformer($slug, $dataTransformer);

        $this->assertSame(
            $dataTransformer,
            $sut->getTransformer($slug)
        );

        $this->assertNull(
            $sut->getTransformer('international-journeys')
        );
    }
}
