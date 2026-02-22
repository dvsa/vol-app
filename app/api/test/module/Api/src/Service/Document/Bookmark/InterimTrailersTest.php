<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\InterimTrailers;

/**
 * Interim trailers test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimTrailersTest extends \PHPUnit\Framework\TestCase
{
    public function testRenderWithNoValueAppliesDefault(): void
    {
        $bookmark = new InterimTrailers();
        $bookmark->setData(
            [
                'interimAuthTrailers' => null

            ]
        );

        $this->assertEquals(0, $bookmark->render());
    }
}
