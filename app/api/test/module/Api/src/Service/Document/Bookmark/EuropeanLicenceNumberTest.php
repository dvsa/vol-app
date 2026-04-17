<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\EuropeanLicenceNumber;

/**
 * European Licence Number test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class EuropeanLicenceNumberTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new EuropeanLicenceNumber();
        $query = $bookmark->getQuery(['communityLic' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new EuropeanLicenceNumber();
        $bookmark->setData(
            [
                'issueNo' => 2,
                'licence' => [
                    'licNo' => 'PD4345'
                ]

            ]
        );

        $this->assertEquals(
            'PD4345/00002',
            $bookmark->render()
        );
    }
}
