<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\CaseworkerName;

/**
 * Case worker name test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class CaseworkerNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new CaseworkerName();
        $query = $bookmark->getQuery(['user' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new CaseworkerName();
        $bookmark->setData(
            [
                'contactDetails' => [
                    'person' => [
                        'forename' => 'Bob',
                        'familyName' => 'Smith'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            'Bob Smith',
            $bookmark->render()
        );
    }
}
