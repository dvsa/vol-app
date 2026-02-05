<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\InsMoreFreqYes;

/**
 * InsMoreFreqYes bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsMoreFreqYesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new InsMoreFreqYes();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('safetyInsProvider')]
    public function testRenderWithInsMoreFreqYes(mixed $flag, mixed $expected): void
    {
        $bookmark = new InsMoreFreqYes();
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
            [1, 'X'],
            [0, '']
        ];
    }
}
