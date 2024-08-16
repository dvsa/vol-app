<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\AddressOfEstablishment;

/**
 * Address of Establishment test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AddressOfEstablishmentTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new AddressOfEstablishment();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoAddressOfEstablishment()
    {
        $bookmark = new AddressOfEstablishment();
        $bookmark->setData(
            [
                'establishmentCd' => null
            ]
        );

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithAddressOfEstablishment()
    {
        $bookmark = new AddressOfEstablishment();
        $bookmark->setData(
            [
                'establishmentCd' => [
                    'address' => [
                        'addressLine1' => 'Line 1'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            'Line 1',
            $bookmark->render()
        );
    }
}
