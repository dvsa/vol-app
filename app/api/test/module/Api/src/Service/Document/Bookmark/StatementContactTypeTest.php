<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\StatementContactType;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Document\Bookmark\StatementContactType::class)]
final class StatementContactTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $id = '123';

        $bookmark = new StatementContactType();

        $query = $bookmark->getQuery(['statement' => $id]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testGetQueryNull(): void
    {
        $sut = new StatementContactType();
        $actual = $sut->getQuery(['statement' => null]);

        $this->assertNull($actual);
    }

    public function testRender(): void
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
