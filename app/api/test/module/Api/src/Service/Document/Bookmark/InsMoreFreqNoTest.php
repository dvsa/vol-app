<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\InsMoreFreqNo;

/**
 * InsMoreFreqNo bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsMoreFreqNoTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new InsMoreFreqNo();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\Group('InsMoreFreqNoTest')]
    #[\PHPUnit\Framework\Attributes\DataProvider('safetyInsProvider')]
    public function testRenderWithInsMoreFreqNo(mixed $flag, mixed $expected): void
    {
        $bookmark = new InsMoreFreqNo();
        $bookmark->setData(
            [
                'safetyInsVaries' => $flag
            ]
        );

        $this->assertEquals(
            $expected,
            $bookmark->render()
        );
    }

    public static function safetyInsProvider(): array
    {
        return [
            [0, 'X'],
            [1, '']
        ];
    }
}
