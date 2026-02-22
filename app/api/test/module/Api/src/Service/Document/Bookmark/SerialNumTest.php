<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\SerialNum;

/**
 * Class SerialNumTest
 *
 * Test the serial number bookmark.
 *
 * @package Dvsa\OlcsTest\Api\Service\Document\Bookmark
 */
class SerialNumTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new SerialNum();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $mock = $this->createMock(\Dvsa\Olcs\Api\Service\Date::class);
        $mock->expects($this->once())
            ->method('getDate')
            ->with('d/m/Y H:i:s')
            ->willReturn('01/02/15 12:34:56');

        $bookmark = new SerialNum();
        $bookmark->setData(
            [
                'licNo' => 123
            ]
        );

        $bookmark->setDateHelper($mock);

        // The date function is used here because there is no easy way to get
        // a reference to the service container.
        $this->assertEquals(
            '123 01/02/15 12:34:56',
            $bookmark->render()
        );
    }
}
