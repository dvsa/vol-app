<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TaNameUppercase;

/**
 * TA Name (uppercase) test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaNameUppercaseTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TaNameUppercase();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new TaNameUppercase();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'name' => 'TA Name 1'
                ]
            ]
        );

        $this->assertEquals(
            'TA NAME 1',
            $bookmark->render()
        );
    }
}
