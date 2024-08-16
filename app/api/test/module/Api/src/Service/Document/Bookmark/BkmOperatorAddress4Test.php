<?php

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
    public function testRender()
    {
        $bookmark = new Sut();
        $this->assertEquals('', $bookmark->render());
    }
}
