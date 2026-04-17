<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\OpName;

/**
 * OpName test
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class OpNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new OpName();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public static function dpRenderValidDataProvider(): array
    {
        return [
            [
                "Testing Test Limited\nT/A: Trading Test Limited",
                [
                    'organisation' => [
                        'name' => 'Testing Test Limited',
                        'tradingNames' => [
                            [
                                'name' => 'Trading Test Limited'
                            ]
                        ],
                    ]
                ]
            ],
            [
                "Testing Test Limited",
                [
                    'organisation' => [
                        'name' => 'Testing Test Limited',
                        'tradingNames' => [],
                    ]
                ]
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderValidDataProvider')]
    public function testRender(mixed $expected, mixed $results): void
    {
        $bookmark = new OpName();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }
}
