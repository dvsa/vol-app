<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\NoDiscsPrinted;

/**
 * No Discs Printed test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class NoDiscsPrintedTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQueryIsNull(): void
    {
        $bookmark = new NoDiscsPrinted();

        $this->assertNull($bookmark->getQuery([]));
    }

    public function testRender(): void
    {
        $bookmark = new NoDiscsPrinted();
        $bookmark->setData(
            [
                'count' => 123
            ]
        );

        $this->assertEquals(
            123,
            $bookmark->render()
        );
    }
}
