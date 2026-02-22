<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Trailers;

/**
 * Trailers test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TrailersTest extends \PHPUnit\Framework\TestCase
{
    public function testRender(): void
    {
        $bookmark = new Trailers();
        $bookmark->setData(
            [
                'totAuthTrailers' => 1234
            ]
        );

        $this->assertEquals(1234, $bookmark->render());
    }
}
