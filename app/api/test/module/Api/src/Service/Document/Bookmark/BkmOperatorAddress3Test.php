<?php

declare(strict_types=1);

/**
 * BkmOperatorAddress3 Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmOperatorAddress3 as Sut;

/**
 * BkmOperatorAddress3 Test
 */
class BkmOperatorAddress3Test extends \PHPUnit\Framework\TestCase
{
    public function testRender(): void
    {
        $bookmark = new Sut();
        $this->assertEquals('', $bookmark->render());
    }
}
