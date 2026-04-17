<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TmName;

/**
 * Transport Manager name bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TmName();
        $query = $bookmark->getQuery(['transportManager' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new TmName();
        $bookmark->setData(
            [
                'homeCd' => [
                    'person' => [
                        'forename' => 'foo',
                        'familyName' => 'bar'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            'foo bar',
            $bookmark->render()
        );
    }
}
