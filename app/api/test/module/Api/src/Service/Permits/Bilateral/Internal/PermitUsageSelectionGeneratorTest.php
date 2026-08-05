<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\PermitUsageSelectionGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * PermitUsageSelectionGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class PermitUsageSelectionGeneratorTest extends MockeryTestCase
{
    private $permitUsageSelectionGenerator;

    #[\Override]
    public function setUp(): void
    {
        $this->permitUsageSelectionGenerator = new PermitUsageSelectionGenerator();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $requiredPermits, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->permitUsageSelectionGenerator->generate($requiredPermits)
        );
    }

    public static function dpGenerate(): \Iterator
    {
        yield [
            [
                'standard-journey_single' => 5,
                'cabotage-journey_single' => 5
            ],
            RefData::JOURNEY_SINGLE
        ];
        yield [
            [
                'standard-journey_single' => 5
            ],
            RefData::JOURNEY_SINGLE
        ];
        yield [
            [
                'cabotage-journey_single' => 5
            ],
            RefData::JOURNEY_SINGLE
        ];
        yield [
            [
                'standard-journey_multiple' => 5,
                'cabotage-journey_multiple' => 5
            ],
            RefData::JOURNEY_MULTIPLE
        ];
        yield [
            [
                'standard-journey_multiple' => 5
            ],
            RefData::JOURNEY_MULTIPLE
        ];
        yield [
            [
                'cabotage-journey_multiple' => 5
            ],
            RefData::JOURNEY_MULTIPLE
        ];
    }

    public function testGenerateException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Found zero or multiple journey types in input data');

        $requiredPermits = [
            'standard-journey_single' => 5,
            'cabotage-journey_multiple' => 2
        ];

        $this->permitUsageSelectionGenerator->generate($requiredPermits);
    }
}
