<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\FeeReqGrantNumber;

/**
 * Fee Request Grant Number test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FeeReqGrantNumberTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new FeeReqGrantNumber();
        $query = $bookmark->getQuery(['fee' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new FeeReqGrantNumber();
        $bookmark->setData(
            [
                'id' => 1234,
                'licence' => [
                    'licNo' => 'OH1'
                ]
            ]
        );

        $this->assertEquals(
            'OH1 / 1234',
            $bookmark->render()
        );
    }
}
