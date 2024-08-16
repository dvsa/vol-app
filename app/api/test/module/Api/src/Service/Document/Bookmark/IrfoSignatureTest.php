<?php

/**
 * IrfoSignature Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoSignature as Sut;

/**
 * IrfoSignature Test
 */
class IrfoSignatureTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $bookmark = new Sut();
        $this->assertEquals('International Road Freight Office', $bookmark->render());
    }
}
