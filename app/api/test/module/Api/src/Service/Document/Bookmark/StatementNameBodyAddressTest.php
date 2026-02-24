<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\StatementNameBodyAddress;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\StatementNameBodyAddress
 */
class StatementNameBodyAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $id = '123';

        $bookmark = new StatementNameBodyAddress();

        $query = $bookmark->getQuery(['statement' => $id]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testGetQueryNull(): void
    {
        $sut = new StatementNameBodyAddress();
        $actual = $sut->getQuery(['statement' => null]);

        static::assertNull($actual);
    }

    public function testRender(): void
    {
        $bookmark = new StatementNameBodyAddress();

        $data = [
            'id' => '123',
            'requestorsBody' => 'Some Body or Business',
            'requestorsContactDetails' => [
                'person' => [
                    'forename' => 'James',
                    'familyName' => 'Smith'
                ],
                'address' => [
                    'addressLine1' => 'A1',
                    'addressLine2' => 'A2',
                    'addressLine3' => 'A3',
                    'addressLine4' => 'A4',
                    'town' => 'A5',
                    'postcode' => 'A6'
                ]
            ]
        ];

        $bookmark->setData($data);

        $result = "James Smith\nSome Body or Business\nA1\nA2\nA3\nA4\nA5\nA6";

        $this->assertEquals($result, $bookmark->render());
    }
}
