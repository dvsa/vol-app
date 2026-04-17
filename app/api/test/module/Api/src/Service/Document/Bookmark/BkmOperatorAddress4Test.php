<?php

declare(strict_types=1);

/**
 * BkmOperatorAddress4 Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmOperatorAddress4 as Sut;

/**
 * BkmOperatorAddress4 Test
 */
class BkmOperatorAddress4Test extends \PHPUnit\Framework\TestCase
{
    public function testRender(): void
    {
        $bookmark = new Sut();
        $this->assertEquals('', $bookmark->render());
    }
}
