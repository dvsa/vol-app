<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\StatementContactType;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\StatementContactType
 */
class StatementContactTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $id = '123';

        $bookmark = new StatementContactType();

        $query = $bookmark->getQuery(['statement' => $id]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testGetQueryNull()
    {
        $sut = new StatementContactType();
        $actual = $sut->getQuery(['statement' => null]);

        static::assertNull($actual);
    }

    public function testRender()
    {
        $bookmark = new StatementContactType();

        $data = [
            'id' => '123',
            'contactType' => [
                'description' => 'Value 1'
            ]
        ];

        $bookmark->setData($data);

        $this->assertEquals('Value 1', $bookmark->render());
    }
}
