<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TmAddress;

/**
 * Transport Manager address bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TmAddress();
        $query = $bookmark->getQuery(['transportManager' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new TmAddress();
        $bookmark->setData(
            [
                'homeCd' => [
                    'address' => [
                        'addressLine1' => 'al1',
                        'addressLine2' => 'al2',
                        'addressLine3' => 'al3',
                        'addressLine4' => 'al4',
                        'town' => 'town',
                        'postcode' => 'postcode'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            "al1\nal2\nal3\nal4\ntown\npostcode",
            $bookmark->render()
        );
    }
}
