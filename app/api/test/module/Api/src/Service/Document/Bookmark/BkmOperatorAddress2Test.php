<?php

/**
 * BkmOperatorAddress2 Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmOperatorAddress2 as Sut;

/**
 * BkmOperatorAddress2 Test
 */
class BkmOperatorAddress2Test extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $bookmark = new Sut();
        $this->assertEquals('', $bookmark->render());
    }
}
