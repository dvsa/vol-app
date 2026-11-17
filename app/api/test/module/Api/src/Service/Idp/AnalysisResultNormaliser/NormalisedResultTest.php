<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp\AnalysisResultNormaliser;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NormalisedResultTest extends TestCase
{
    private const ROWS = ['bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []]];

    public function testTheArrayFormCarriesTheCurrentVersion(): void
    {
        $this->assertSame(
            ['version' => NormalisedResult::VERSION, 'rows' => self::ROWS],
            NormalisedResult::fromRows(self::ROWS)->toArray()
        );
    }

    public function testRoundTripsThroughItsArrayForm(): void
    {
        $result = NormalisedResult::fromRows(self::ROWS);

        $this->assertSame($result->toArray(), NormalisedResult::fromArray($result->toArray())?->toArray());
        $this->assertSame(self::ROWS, $result->rows());
    }

    /** Only the current shape is accepted; other versions must come through the version mappers. */
    #[DataProvider('rejectedPayloadProvider')]
    public function testRejectsAnythingButTheCurrentShape(array $payload): void
    {
        $this->assertNull(NormalisedResult::fromArray($payload));
    }

    public static function rejectedPayloadProvider(): array
    {
        return [
            'older version' => [['version' => NormalisedResult::VERSION - 1, 'rows' => self::ROWS]],
            'newer version' => [['version' => NormalisedResult::VERSION + 1, 'rows' => self::ROWS]],
            'version as a string' => [['version' => (string)NormalisedResult::VERSION, 'rows' => self::ROWS]],
            'no rows' => [['version' => NormalisedResult::VERSION]],
            'rows not a map' => [['version' => NormalisedResult::VERSION, 'rows' => 'none']],
        ];
    }
}
